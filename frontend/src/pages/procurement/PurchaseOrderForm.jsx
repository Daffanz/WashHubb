import React, { useState, useEffect, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getPurchaseOrder, createPurchaseOrder, updatePurchaseOrder } from '../../api/purchaseOrders';
import { getSuppliers, getSupplierItems } from '../../api/suppliers';
import PageHeader from '../../components/ui/PageHeader';
import FormSelect from '../../components/ui/FormSelect';
import FormActions from '../../components/ui/FormActions';
import { formatRupiah } from '../../utils/format';

export default function PurchaseOrderForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;

  const [form, setForm] = useState({ supplier_id: '', jenis_po: 'bahan_baku' });
  const [items, setItems] = useState([]);
  const [allSuppliers, setAllSuppliers] = useState([]);
  const [supplierItems, setSupplierItems] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEdit);

  // Load all suppliers
  useEffect(() => {
    const token = localStorage.getItem('washhub_token');
    const headers = { Authorization: `Bearer ${token}` };
    getSuppliers({ per_page: 100 }).then((res) => {
      setAllSuppliers(res.data.data || []);
    });
  }, []);

  // Load edit data
  useEffect(() => {
    if (isEdit) {
      getPurchaseOrder(id).then((res) => {
        const po = res.data.data;
        setForm({ supplier_id: po.supplier?.id || '', jenis_po: po.jenis_po || 'bahan_baku' });
        // Build items from backend data
        const allItems = [...(po.items_bahan_baku || []), ...(po.items_mesin || [])];
        setItems(allItems.map((it) => ({
          item_id: it.id, nama: it.nama, jumlah: it.jumlah?.toString() || '',
          harga_satuan: it.harga_satuan?.toString() || '',
        })));
        // Load supplier items so dropdowns have options
        if (po.supplier?.id) {
          const token = localStorage.getItem('washhub_token');
          fetch(`/api/suppliers/${po.supplier.id}/items`, { headers: { Authorization: `Bearer ${token}` } })
            .then((r) => r.json()).then((d) => setSupplierItems(d.data || []));
        }
      }).catch(() => toast.error('Gagal memuat data')).finally(() => setFetching(false));
    }
  }, [id, isEdit]);

  // Filter suppliers by jenis_po
  const filteredSuppliers = useMemo(() => {
    const jenisMap = { bahan_baku: 'bahan_baku', mesin: 'mesin' };
    const targetJenis = jenisMap[form.jenis_po] || 'bahan_baku';
    let filtered = allSuppliers
      .filter((s) => s.jenis_supplier === targetJenis)
      .map((s) => ({
        value: s.id,
        label: `${s.user?.nama || 'Supplier'} (${s.jenis_supplier === 'bahan_baku' ? (s.bahan_bakus?.length || 0) + ' produk' : (s.mesins?.length || 0) + ' produk'})`,
      }));
    // On edit, ensure current supplier is in the list
    if (isEdit && form.supplier_id && !filtered.find((s) => s.value === form.supplier_id)) {
      const current = allSuppliers.find((s) => s.id === form.supplier_id);
      if (current) {
        filtered = [{ value: current.id, label: current.user?.nama || 'Supplier' }, ...filtered];
      }
    }
    return filtered;
  }, [allSuppliers, form.jenis_po, form.supplier_id, isEdit]);

  // When supplier is selected, load their items
  useEffect(() => {
    if (form.supplier_id) {
      const token = localStorage.getItem('washhub_token');
      fetch(`/api/suppliers/${form.supplier_id}/items`, { headers: { Authorization: `Bearer ${token}` } })
        .then((r) => r.json())
        .then((d) => {
          setSupplierItems(d.data || []);
          // Reset items when supplier changes (unless editing)
          if (!isEdit) {
            setItems([]);
          }
        })
        .catch(() => setSupplierItems([]));
    } else {
      setSupplierItems([]);
    }
  }, [form.supplier_id, isEdit]);

  const handleFormChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
    if (name === 'jenis_po') {
      setForm((prev) => ({ ...prev, supplier_id: '' }));
      setItems([]);
    }
    if (name === 'supplier_id') {
      setItems([]);
    }
  };

  const handleItemChange = (index, field, value) => {
    const newItems = [...items];
    newItems[index] = { ...newItems[index], [field]: value };
    setItems(newItems);
  };

  const selectItem = (index, itemId) => {
    const found = supplierItems.find((si) => si.id.toString() === itemId.toString());
    const newItems = [...items];
    newItems[index] = {
      ...newItems[index],
      item_id: itemId,
      nama: found?.nama || '',
      harga_satuan: found?.harga_standar?.toString() || '',
    };
    setItems(newItems);
  };

  const addItem = () => setItems([...items, { item_id: '', nama: '', jumlah: '', harga_satuan: '' }]);
  const removeItem = (i) => {
    const n = items.filter((_, idx) => idx !== i);
    setItems(n);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const payload = {
        supplier_id: parseInt(form.supplier_id),
        jenis_po: form.jenis_po,
        items: items.map((it) => ({
          item_id: parseInt(it.item_id),
          jumlah: parseFloat(it.jumlah),
          harga_satuan: parseFloat(it.harga_satuan),
        })),
      };
      const url = isEdit ? `/api/procurement/purchase-orders/${id}` : '/api/procurement/purchase-orders';
      const method = isEdit ? 'PUT' : 'POST';
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${localStorage.getItem('washhub_token')}` },
        body: JSON.stringify(payload),
      });
      if (!res.ok) { const d = await res.json(); setErrors(d.errors || {}); throw new Error(d.message); }
      toast.success(isEdit ? 'PO berhasil diperbarui' : 'PO berhasil dibuat');
      navigate('/procurement/purchase-orders');
    } catch (err) { toast.error(err.message || 'Terjadi kesalahan'); }
    setLoading(false);
  };

  if (fetching) return <div className="flex justify-center py-16"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>;

  const total = items.reduce((sum, it) => sum + (parseFloat(it.jumlah) || 0) * (parseFloat(it.harga_satuan) || 0), 0);

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit PO' : 'Buat PO Baru'} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'PO', to: '/procurement/purchase-orders' }, { label: isEdit ? 'Edit' : 'Buat' }]} />
      <form onSubmit={handleSubmit}>
        {/* Info PO */}
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Informasi PO</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            <FormSelect label="Jenis PO" name="jenis_po" value={form.jenis_po} onChange={handleFormChange} options={[{ value: 'bahan_baku', label: 'Bahan Baku' }, { value: 'mesin', label: 'Mesin' }]} required />
            <FormSelect label="Supplier" name="supplier_id" value={form.supplier_id} onChange={handleFormChange} options={filteredSuppliers} error={errors.supplier_id?.[0]} required placeholder={filteredSuppliers.length === 0 ? 'Tidak ada supplier tersedia' : 'Pilih supplier'} />
          </div>
          {form.jenis_po && filteredSuppliers.length === 0 && (
            <p className="mt-2 text-xs text-amber-600 bg-amber-50 px-3 py-2 rounded-lg">
              Tidak ada supplier dengan jenis "{form.jenis_po === 'bahan_baku' ? 'Bahan Baku' : 'Mesin'}". Buat supplier dulu di menu Supplier.
            </p>
          )}
        </div>

        {/* Items */}
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-sm font-semibold text-gray-900">Items</h3>
            <button type="button" onClick={addItem} disabled={!form.supplier_id} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-3 py-1.5 rounded-lg border border-wash-200 hover:bg-wash-50 transition disabled:opacity-40 disabled:cursor-not-allowed">+ Tambah Item</button>
          </div>

          {!form.supplier_id ? (
            <p className="text-sm text-gray-400 text-center py-8">Pilih supplier terlebih dahulu</p>
          ) : items.length === 0 ? (
            <p className="text-sm text-gray-400 text-center py-8">Belum ada item. Klik "Tambah Item" untuk menambah.</p>
          ) : (
            <div className="space-y-4">
              {items.map((item, i) => {
                const sub = (parseFloat(item.jumlah) || 0) * (parseFloat(item.harga_satuan) || 0);
                return (
                  <div key={i} className="p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <div className="flex flex-col sm:flex-row gap-3 items-end">
                      <div className="flex-1 w-full">
                        <label className="block text-xs font-medium text-gray-500 mb-1">Item *</label>
                        <select value={item.item_id} onChange={(e) => selectItem(i, e.target.value)} className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-wash-500">
                          <option value="">Pilih item</option>
                          {supplierItems.map((si) => (
                            <option key={si.id} value={si.id}>{si.nama} {si.satuan ? `(${si.satuan})` : ''} — {formatRupiah(si.harga_standar)}</option>
                          ))}
                        </select>
                      </div>
                      <div className="w-full sm:w-24">
                        <label className="block text-xs font-medium text-gray-500 mb-1">Jumlah *</label>
                        <input type="number" value={item.jumlah} onChange={(e) => handleItemChange(i, 'jumlah', e.target.value)} className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-wash-500" placeholder="0" step="any" />
                      </div>
                      <div className="w-full sm:w-36">
                        <label className="block text-xs font-medium text-gray-500 mb-1">Harga Satuan *</label>
                        <input type="number" value={item.harga_satuan} onChange={(e) => handleItemChange(i, 'harga_satuan', e.target.value)} className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-wash-500" placeholder="Otomatis" step="any" />
                      </div>
                      <button type="button" onClick={() => removeItem(i)} className="px-3 py-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition text-sm">Hapus</button>
                    </div>
                    {item.jumlah && item.harga_satuan && (
                      <div className="mt-2 text-xs text-gray-500 text-right">
                        Subtotal: <span className="font-semibold text-gray-700">{formatRupiah(sub)}</span>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          )}

          {items.some((it) => it.jumlah && it.harga_satuan) && (
            <div className="mt-4 pt-4 border-t border-gray-200 flex justify-end">
              <span className="text-sm font-semibold text-gray-900">Total: {formatRupiah(total)}</span>
            </div>
          )}
          {errors.items && <p className="mt-2 text-xs text-red-600">{typeof errors.items === 'string' ? errors.items : 'Ada item yang belum lengkap'}</p>}
          <FormActions loading={loading} />
        </div>
      </form>
    </div>
  );
}

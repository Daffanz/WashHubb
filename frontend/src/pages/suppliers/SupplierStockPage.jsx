import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import FormActions from '../../components/ui/FormActions';
import ConfirmModal from '../../components/ui/ConfirmModal';
import { getSupplierStocks, addSupplierStock, deleteSupplierStock } from '../../api/supplierStocks';
import { getMaterials } from '../../api/materials';
import { getMachines } from '../../api/machines';
import { formatQty } from '../../utils/format';

export default function SupplierStockPage() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [jenisSupplier, setJenisSupplier] = useState(null);
  const [showForm, setShowForm] = useState(false);
  const [items, setItems] = useState([]);
  const [masterItems, setMasterItems] = useState([]);
  const [submitting, setSubmitting] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleting, setDeleting] = useState(false);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try {
      const res = await getSupplierStocks({ page: p });
      setData(res.data.data || []); setMeta(res.data.meta);
      if (res.data.jenis_supplier) setJenisSupplier(res.data.jenis_supplier);
    } catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  // Load master items when form opens
  useEffect(() => {
    if (showForm && jenisSupplier) {
      if (jenisSupplier === 'bahan_baku') {
        getMaterials({ per_page: 200 }).then(res => setMasterItems(res.data.data || [])).catch(() => {});
      } else {
        getMachines({ per_page: 200 }).then(res => setMasterItems(res.data.data || [])).catch(() => {});
      }
    }
  }, [showForm, jenisSupplier]);

  const addItem = () => setItems([...items, { item_id: '', jumlah: '' }]);
  const removeItem = (i) => setItems(items.filter((_, idx) => idx !== i));
  const updateItem = (i, field, value) => {
    const n = [...items]; n[i][field] = value; setItems(n);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const validItems = items.filter(i => i.item_id && parseFloat(i.jumlah) > 0);
    if (validItems.length === 0) { toast.error('Minimal 1 item dengan jumlah > 0'); return; }

    setSubmitting(true);
    try {
      await addSupplierStock({ items: validItems.map(i => ({ item_id: parseInt(i.item_id), jumlah: parseFloat(i.jumlah) })) });
      toast.success(`${validItems.length} item berhasil ditambahkan`);
      setShowForm(false); setItems([]);
      fetchData();
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setSubmitting(false);
  };

  const handleDelete = async () => {
    setDeleting(true);
    try {
      await deleteSupplierStock(deleteTarget.id);
      toast.success('Stok berhasil dihapus'); setDeleteTarget(null); fetchData();
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setDeleting(false);
  };

  const columns = jenisSupplier === 'bahan_baku' ? [
    { label: 'Bahan Baku', render: (r) => <span className="font-medium">{r.bahan_baku?.nama || '-'}</span> },
    { label: 'Kategori', render: (r) => r.bahan_baku?.kategori || '-' },
    { label: 'Satuan', render: (r) => r.bahan_baku?.satuan || '-' },
    { label: 'Stok', render: (r) => <span className="font-bold text-wash-800">{formatQty(r.stok_saat_ini)}</span> },
  ] : [
    { label: 'Mesin', render: (r) => <span className="font-medium">{r.mesin?.nama || '-'}</span> },
    { label: 'Kode', render: (r) => r.mesin?.kode_mesin || '-' },
    { label: 'Stok', render: (r) => <span className="font-bold text-wash-800">{formatQty(r.stok_saat_ini)}</span> },
  ];

  return (
    <div>
      <PageHeader
        title="Stok Supplier"
        breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Stok Supplier' }]}
        actionLabel={showForm ? '✕ Tutup Form' : '+ Tambah Stok'}
        actionOnClick={() => {
          setShowForm(!showForm);
          if (!showForm) setItems([{ item_id: '', jumlah: '' }]);
          else setItems([]);
        }}
      />

      {/* Form tambah stok multi-item */}
      {showForm && (
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-2">
            Tambah Stok ({jenisSupplier === 'bahan_baku' ? 'Bahan Baku' : 'Mesin'})
          </h3>
          <p className="text-xs text-gray-500 mb-4">Jenis item otomatis sesuai jenis supplier Anda. Bisa tambah banyak item sekaligus.</p>

          {items.length === 0 && (
            <p className="text-sm text-gray-400 text-center py-4">Klik "+ Tambah Item" di bawah</p>
          )}

          {items.map((item, i) => (
            <div key={i} className="flex items-center gap-3 mb-2 p-3 bg-gray-50 rounded-lg">
              <div className="flex-1">
                <select value={item.item_id} onChange={(e) => updateItem(i, 'item_id', e.target.value)}
                  className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-wash-500">
                  <option value="">{jenisSupplier === 'bahan_baku' ? 'Pilih bahan baku...' : 'Pilih mesin...'}</option>
                  {masterItems.map(mi => (
                    <option key={mi.id} value={mi.id}>{mi.nama} {mi.satuan ? `(${mi.satuan})` : ''}</option>
                  ))}
                </select>
              </div>
              <div className="w-28">
                <input type="number" value={item.jumlah} onChange={(e) => updateItem(i, 'jumlah', e.target.value)}
                  className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-wash-500"
                  placeholder="Jumlah" step="any" min="1" />
              </div>
              <button type="button" onClick={() => removeItem(i)}
                className="px-2 py-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition text-sm">✕</button>
            </div>
          ))}

          <div className="flex gap-2 mt-3 mb-4">
            <button type="button" onClick={addItem}
              className="text-sm text-wash-700 hover:text-wash-900 font-medium px-3 py-1.5 rounded-lg border border-wash-200 hover:bg-wash-50 transition">
              + Tambah Item
            </button>
          </div>

          <div className="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="button" onClick={() => { setShowForm(false); setItems([]); }}
              className="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
              Batal
            </button>
            <button type="button" onClick={handleSubmit} disabled={submitting || items.length === 0}
              className="px-5 py-2.5 text-sm font-medium text-white bg-wash-900 rounded-lg hover:bg-wash-800 transition shadow-sm disabled:opacity-50">
              {submitting ? 'Menyimpan...' : 'Simpan'}
            </button>
          </div>
        </div>
      )}

      {/* Tabel stok */}
      {loading ? (
        <div className="flex justify-center py-16"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>
      ) : (
        <DataTable columns={columns} data={data} loading={false} meta={meta} onPageChange={(p) => setPage(p)}
          actions={(row) => (
            <div className="flex gap-1 justify-end">
              <Link to={`/suppliers/stock/${row.id}`} className="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50">Detail</Link>
              <button onClick={() => setDeleteTarget(row)}
                className="text-xs text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50">
                Hapus
              </button>
            </div>
          )}
        />
      )}

      <ConfirmModal open={!!deleteTarget} title="Hapus Stok"
        message={`Yakin hapus stok "${deleteTarget?.bahan_baku?.nama || deleteTarget?.mesin?.nama}"? Stok: ${deleteTarget?.stok_saat_ini}`}
        onConfirm={handleDelete} onCancel={() => setDeleteTarget(null)} loading={deleting} />
    </div>
  );
}

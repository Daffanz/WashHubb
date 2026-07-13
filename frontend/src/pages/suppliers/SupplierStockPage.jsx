import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import ConfirmModal from '../../components/ui/ConfirmModal';
import { getSupplierStocks, addSupplierStock, updateSupplierStock, deleteSupplierStock } from '../../api/supplierStocks';
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
  const [editTarget, setEditTarget] = useState(null);
  const [editJumlah, setEditJumlah] = useState('');
  const [editing, setEditing] = useState(false);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try {
      const res = await getSupplierStocks({ page: p });
      setData(res.data.data || []); setMeta(res.data.meta);
      if (res.data.jenis_supplier) {
        setJenisSupplier(res.data.jenis_supplier);
        // Load master items immediately when jenisSupplier is known
        if (res.data.jenis_supplier === 'bahan_baku') {
          getMaterials({ per_page: 200 }).then(r => setMasterItems(r.data.data || [])).catch(err => console.error('Gagal load bahan baku:', err));
        } else {
          getMachines({ per_page: 200 }).then(r => setMasterItems(r.data.data || [])).catch(err => console.error('Gagal load mesin:', err));
        }
      }
    } catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const addItem = () => setItems([...items, { item_id: '', jumlah: '' }]);
  const removeItem = (i) => setItems(items.filter((_, idx) => idx !== i));
  const updateItem = (i, field, value) => {
    const n = [...items]; n[i][field] = value; setItems(n);
  };

  const getSelectedItemInfo = (itemId) => {
    return masterItems.find(mi => String(mi.id) === String(itemId));
  };

  const handleSubmit = async () => {
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

  const handleEdit = async () => {
    const jumlah = parseFloat(editJumlah);
    if (!jumlah || jumlah <= 0) { toast.error('Jumlah harus lebih dari 0'); return; }

    setEditing(true);
    try {
      await updateSupplierStock(editTarget.id, { jumlah });
      toast.success('Stok berhasil ditambahkan');
      setEditTarget(null); setEditJumlah('');
      fetchData();
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setEditing(false);
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
          <p className="text-xs text-gray-500 mb-4">Pilih {jenisSupplier === 'bahan_baku' ? 'bahan baku' : 'mesin'} dari data master, lalu masukkan jumlah stok. Bisa tambah banyak item sekaligus.</p>

          {items.length === 0 && (
            <p className="text-sm text-gray-400 text-center py-4">Klik "+ Tambah Item" di bawah</p>
          )}

          {items.map((item, i) => {
            const selectedInfo = getSelectedItemInfo(item.item_id);
            return (
              <div key={i} className="mb-3 p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div className="flex items-center justify-between mb-3">
                  <span className="text-xs font-semibold text-gray-500">Item #{i + 1}</span>
                  <button type="button" onClick={() => removeItem(i)}
                    className="text-xs text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded transition">✕ Hapus</button>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-3">
                  <div className="md:col-span-2">
                    <label className="block text-xs font-medium text-gray-600 mb-1">
                      {jenisSupplier === 'bahan_baku' ? 'Bahan Baku' : 'Mesin'}
                    </label>
                    <select value={item.item_id} onChange={(e) => updateItem(i, 'item_id', e.target.value)}
                      className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-wash-500">
                      <option value="">{jenisSupplier === 'bahan_baku' ? 'Pilih bahan baku...' : 'Pilih mesin...'}</option>
                      {masterItems.map(mi => (
                        <option key={mi.id} value={mi.id}>{mi.nama} {mi.satuan ? `(${mi.satuan})` : ''} {mi.kode_mesin ? `- ${mi.kode_mesin}` : ''}</option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-600 mb-1">Jumlah Stok</label>
                    <input type="number" value={item.jumlah} onChange={(e) => updateItem(i, 'jumlah', e.target.value)}
                      className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-wash-500"
                      placeholder="0" step="any" min="1" />
                  </div>
                  <div className="flex items-end">
                    {selectedInfo && (
                      <div className="text-xs text-gray-500 pb-2">
                        {selectedInfo.kategori?.nama && <span className="inline-block bg-gray-200 rounded px-1.5 py-0.5 mr-1">{selectedInfo.kategori.nama}</span>}
                        {selectedInfo.satuan && <span className="text-gray-400">{selectedInfo.satuan}</span>}
                        {selectedInfo.harga_standar != null && <span className="text-gray-400 ml-1">Rp {Number(selectedInfo.harga_standar).toLocaleString('id-ID')}</span>}
                      </div>
                    )}
                  </div>
                </div>
              </div>
            );
          })}

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
              <button onClick={() => { setEditTarget(row); setEditJumlah(''); }}
                className="text-xs text-emerald-600 hover:text-emerald-800 font-medium px-2 py-1 rounded hover:bg-emerald-50">
                + Stok
              </button>
              <Link to={`/suppliers/stock/${row.id}`} className="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50">Detail</Link>
              <button onClick={() => setDeleteTarget(row)}
                className="text-xs text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50">
                Hapus
              </button>
            </div>
          )}
        />
      )}

      {/* Modal tambah stok per item */}
      {editTarget && (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
          <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={() => setEditTarget(null)}></div>
          <div className="relative bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-1">Tambah Stok</h3>
            <p className="text-sm text-gray-500 mb-4">
              {editTarget.bahan_baku?.nama || editTarget.mesin?.nama || 'Item'}
              {editTarget.bahan_baku?.satuan && <span className="text-gray-400"> ({editTarget.bahan_baku.satuan})</span>}
            </p>
            <p className="text-xs text-gray-400 mb-3">Stok saat ini: <span className="font-semibold text-wash-800">{formatQty(editTarget.stok_saat_ini)}</span></p>
            <input type="number" value={editJumlah} onChange={(e) => setEditJumlah(e.target.value)}
              className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-wash-500 mb-4"
              placeholder="Jumlah yang ditambahkan" step="any" min="1" autoFocus />
            <div className="flex justify-end gap-3">
              <button onClick={() => setEditTarget(null)}
                className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Batal
              </button>
              <button onClick={handleEdit} disabled={editing}
                className="px-4 py-2 text-sm font-medium text-white bg-wash-900 rounded-lg hover:bg-wash-800 transition disabled:opacity-50">
                {editing ? 'Menyimpan...' : 'Tambah Stok'}
              </button>
            </div>
          </div>
        </div>
      )}

      <ConfirmModal open={!!deleteTarget} title="Hapus Stok"
        message={`Yakin hapus stok "${deleteTarget?.bahan_baku?.nama || deleteTarget?.mesin?.nama}"? Stok: ${deleteTarget?.stok_saat_ini}`}
        onConfirm={handleDelete} onCancel={() => setDeleteTarget(null)} loading={deleting} />
    </div>
  );
}

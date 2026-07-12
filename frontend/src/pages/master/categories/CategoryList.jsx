import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getCategories, deleteCategory } from '../../../api/categories';
import PageHeader from '../../../components/ui/PageHeader';
import DataTable from '../../../components/ui/DataTable';
import StatusBadge from '../../../components/ui/StatusBadge';
import ConfirmModal from '../../../components/ui/ConfirmModal';

export default function CategoryList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleting, setDeleting] = useState(false);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getCategories({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const handleDelete = async () => {
    setDeleting(true);
    try { await deleteCategory(deleteTarget.id); toast.success('Kategori berhasil dihapus'); setDeleteTarget(null); fetchData(); }
    catch { toast.error('Gagal menghapus kategori'); }
    setDeleting(false);
  };

  const columns = [
    { label: 'Nama', key: 'nama' },
    { label: 'Jumlah Bahan Baku', render: (row) => row.bahan_bakus_count ?? 0 },
  ];

  return (
    <div>
      <PageHeader title="Kategori Bahan Baku" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Master Data' }, { label: 'Kategori' }]} actionLabel="Tambah Kategori" actionTo="/master/categories/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <div className="flex gap-2 justify-end">
            <Link to={`/master/categories/${row.id}/edit`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Edit</Link>
            <button onClick={() => setDeleteTarget(row)} className="text-sm text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50 transition">Hapus</button>
          </div>
        )}
      />
      <ConfirmModal open={!!deleteTarget} title="Hapus Kategori" message={`Yakin ingin menghapus kategori "${deleteTarget?.nama}"?`} onConfirm={handleDelete} onCancel={() => setDeleteTarget(null)} loading={deleting} />
    </div>
  );
}

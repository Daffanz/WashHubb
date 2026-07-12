import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getMaterials, deleteMaterial } from '../../../api/materials';
import PageHeader from '../../../components/ui/PageHeader';
import DataTable from '../../../components/ui/DataTable';
import StatusBadge from '../../../components/ui/StatusBadge';
import ConfirmModal from '../../../components/ui/ConfirmModal';
import { formatRupiah } from '../../../utils/format';

export default function MaterialList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleting, setDeleting] = useState(false);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getMaterials({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const handleDelete = async () => {
    setDeleting(true);
    try { await deleteMaterial(deleteTarget.id); toast.success('Bahan baku berhasil dihapus'); setDeleteTarget(null); fetchData(); }
    catch { toast.error('Gagal menghapus bahan baku'); }
    setDeleting(false);
  };

  const columns = [
    { label: 'Nama', key: 'nama' },
    { label: 'Kategori', render: (row) => row.kategori?.nama || '-' },
    { label: 'Satuan', key: 'satuan' },
    { label: 'Harga Standar', render: (row) => formatRupiah(row.harga_standar) },
    { label: 'Status', render: (row) => <StatusBadge status={row.status?.label} color={row.status?.kode === 'aktif' ? 'green' : 'red'} /> },
  ];

  return (
    <div>
      <PageHeader title="Bahan Baku" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Master Data' }, { label: 'Bahan Baku' }]} actionLabel="Tambah Bahan Baku" actionTo="/master/materials/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <div className="flex gap-2 justify-end">
            <Link to={`/master/materials/${row.id}/edit`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Edit</Link>
            <button onClick={() => setDeleteTarget(row)} className="text-sm text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50 transition">Hapus</button>
          </div>
        )}
      />
      <ConfirmModal open={!!deleteTarget} title="Hapus Bahan Baku" message={`Yakin ingin menghapus "${deleteTarget?.nama}"?`} onConfirm={handleDelete} onCancel={() => setDeleteTarget(null)} loading={deleting} />
    </div>
  );
}

import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getServices, deleteService } from '../../../api/services';
import PageHeader from '../../../components/ui/PageHeader';
import DataTable from '../../../components/ui/DataTable';
import StatusBadge from '../../../components/ui/StatusBadge';
import ConfirmModal from '../../../components/ui/ConfirmModal';
import { formatRupiah } from '../../../utils/format';

export default function ServiceList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleting, setDeleting] = useState(false);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getServices({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const handleDelete = async () => {
    setDeleting(true);
    try { await deleteService(deleteTarget.id); toast.success('Layanan berhasil dihapus'); setDeleteTarget(null); fetchData(); }
    catch { toast.error('Gagal menghapus layanan'); }
    setDeleting(false);
  };

  const columns = [
    { label: 'Nama', key: 'nama' },
    { label: 'Harga/kg', render: (row) => formatRupiah(row.harga_standar_per_kg) },
    { label: 'Status', render: (row) => <StatusBadge status={row.status?.label} color={row.status?.kode === 'aktif' ? 'green' : 'red'} /> },
  ];

  return (
    <div>
      <PageHeader title="Jenis Layanan" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Master Data' }, { label: 'Layanan' }]} actionLabel="Tambah Layanan" actionTo="/master/services/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <div className="flex gap-2 justify-end">
            <Link to={`/master/services/${row.id}`} className="text-sm text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50 transition">Detail</Link>
            <Link to={`/master/services/${row.id}/edit`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Edit</Link>
            <button onClick={() => setDeleteTarget(row)} className="text-sm text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50 transition">Hapus</button>
          </div>
        )}
      />
      <ConfirmModal open={!!deleteTarget} title="Hapus Layanan" message={`Yakin ingin menghapus "${deleteTarget?.nama}"?`} onConfirm={handleDelete} onCancel={() => setDeleteTarget(null)} loading={deleting} />
    </div>
  );
}

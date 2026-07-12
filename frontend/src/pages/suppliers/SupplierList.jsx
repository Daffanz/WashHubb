import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getSuppliers, deleteSupplier } from '../../api/suppliers';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import StatusBadge from '../../components/ui/StatusBadge';
import ConfirmModal from '../../components/ui/ConfirmModal';

export default function SupplierList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleting, setDeleting] = useState(false);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getSuppliers({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const handleDelete = async () => {
    setDeleting(true);
    try { await deleteSupplier(deleteTarget.id); toast.success('Supplier berhasil dihapus'); setDeleteTarget(null); fetchData(); }
    catch { toast.error('Gagal menghapus supplier'); }
    setDeleting(false);
  };

  const columns = [
    { label: 'Nama User', render: (row) => row.user?.nama || '-' },
    { label: 'Jenis Supplier', render: (row) => <span className="capitalize">{row.jenis_supplier?.replace('_', ' ')}</span> },
    { label: 'Alamat', render: (row) => <span className="max-w-[200px] truncate inline-block">{row.alamat}</span> },
    { label: 'Status', render: (row) => <StatusBadge status={row.status?.label} color={row.status?.kode === 'aktif' ? 'green' : 'red'} /> },
  ];

  return (
    <div>
      <PageHeader title="Suppliers" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Suppliers' }]} actionLabel="Tambah Supplier" actionTo="/suppliers/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <div className="flex gap-2 justify-end">
            <Link to={`/suppliers/${row.id}/edit`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Edit</Link>
            <button onClick={() => setDeleteTarget(row)} className="text-sm text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50 transition">Hapus</button>
          </div>
        )}
      />
      <ConfirmModal open={!!deleteTarget} title="Hapus Supplier" message={`Yakin ingin menghapus supplier ini?`} onConfirm={handleDelete} onCancel={() => setDeleteTarget(null)} loading={deleting} />
    </div>
  );
}

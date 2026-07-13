import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getUsers, deleteUser } from '../../api/users';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import StatusBadge from '../../components/ui/StatusBadge';
import ConfirmModal from '../../components/ui/ConfirmModal';
import { formatDate } from '../../utils/format';

export default function UserList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleting, setDeleting] = useState(false);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getUsers({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const handleDelete = async () => {
    setDeleting(true);
    try { await deleteUser(deleteTarget.id); toast.success('User berhasil dihapus'); setDeleteTarget(null); fetchData(); }
    catch { toast.error('Gagal menghapus user'); }
    setDeleting(false);
  };

  const columns = [
    { label: 'Nama', key: 'nama' },
    { label: 'Email', key: 'email' },
    { label: 'Role', render: (row) => row.role?.label || '-' },
    { label: 'Status', render: (row) => <StatusBadge status={row.status?.label} color={row.status?.kode === 'aktif' ? 'green' : 'red'} /> },
    { label: 'Dibuat', render: (row) => formatDate(row.created_at) },
  ];

  return (
    <div>
      <PageHeader title="Users" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Users' }]} actionLabel="Tambah User" actionTo="/users/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <div className="flex gap-2 justify-end">
            <Link to={`/users/${row.id}/edit`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Edit</Link>
            {!['admin_it', 'franchise', 'manager_outlet'].includes(row.role?.kode) && (
              <button onClick={() => setDeleteTarget(row)} className="text-sm text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50 transition">Hapus</button>
            )}
          </div>
        )}
      />
      <ConfirmModal open={!!deleteTarget} title="Hapus User" message={`Yakin ingin menghapus user "${deleteTarget?.nama}"?`} onConfirm={handleDelete} onCancel={() => setDeleteTarget(null)} loading={deleting} />
    </div>
  );
}

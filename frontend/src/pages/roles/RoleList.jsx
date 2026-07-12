import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getRoles, deleteRole } from '../../api/roles';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import ConfirmModal from '../../components/ui/ConfirmModal';

export default function RoleList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleting, setDeleting] = useState(false);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getRoles({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const handleDelete = async () => {
    setDeleting(true);
    try { await deleteRole(deleteTarget.id); toast.success('Role berhasil dihapus'); setDeleteTarget(null); fetchData(); }
    catch { toast.error('Gagal menghapus role'); }
    setDeleting(false);
  };

  const columns = [
    { label: 'Kode', render: (r) => <span className="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{r.kode}</span> },
    { label: 'Label', key: 'label' },
    { label: 'Permissions', render: (r) => r.permissions?.length || 0 },
  ];

  return (
    <div>
      <PageHeader title="Roles" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Roles' }]} actionLabel="Tambah Role" actionTo="/roles/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <div className="flex gap-2 justify-end">
            <Link to={`/roles/${row.id}/edit`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Edit</Link>
            <button onClick={() => setDeleteTarget(row)} className="text-sm text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50 transition">Hapus</button>
          </div>
        )}
      />
      <ConfirmModal open={!!deleteTarget} title="Hapus Role" message={`Yakin ingin menghapus role "${deleteTarget?.label}"?`} onConfirm={handleDelete} onCancel={() => setDeleteTarget(null)} loading={deleting} />
    </div>
  );
}

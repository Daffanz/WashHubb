import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getOutlets } from '../../../api/outlets';
import PageHeader from '../../../components/ui/PageHeader';
import DataTable from '../../../components/ui/DataTable';
import StatusBadge from '../../../components/ui/StatusBadge';

export default function OutletList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getOutlets({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'Nama', key: 'nama' },
    { label: 'Kode', render: (r) => <span className="font-mono text-sm">{r.kode_outlet}</span> },
    { label: 'Alamat', render: (r) => <span className="text-gray-500 text-sm truncate max-w-xs block">{r.alamat}</span> },
    { label: 'Franchise', render: (r) => r.franchise?.user?.nama || '-' },
    { label: 'Status', render: (r) => <StatusBadge status={r.status?.label} color={r.status?.kode === 'aktif' ? 'green' : 'red'} /> },
  ];

  return (
    <div>
      <PageHeader title="Outlet" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Franchise' }, { label: 'Outlet' }]} actionLabel="Tambah Outlet" actionTo="/franchise/outlets/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <div className="flex gap-2 justify-end">
            <Link to={`/franchise/outlets/${row.id}`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Detail</Link>
            <Link to={`/franchise/outlets/${row.id}/edit`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Edit</Link>
          </div>
        )}
      />
    </div>
  );
}

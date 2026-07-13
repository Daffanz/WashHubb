import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getDistribusis } from '../../../api/distribusiOutlet';
import PageHeader from '../../../components/ui/PageHeader';
import DataTable from '../../../components/ui/DataTable';
import StatusBadge from '../../../components/ui/StatusBadge';
import { formatDate } from '../../../utils/format';

export default function DistribusiList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getDistribusis({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'ID', render: (r) => <span className="font-mono text-sm">#{r.id}</span> },
    { label: 'Permintaan', render: (r) => r.permintaan?.id ? `#${r.permintaan.id}` : '-' },
    { label: 'Outlet', render: (r) => r.permintaan?.outlet || '-' },
    { label: 'Tanggal Kirim', render: (r) => formatDate(r.tanggal_kirim) },
    { label: 'Items', render: (r) => <span className="text-sm text-gray-500">{r.details?.length || 0} item</span> },
    { label: 'Status', render: (r) => {
      const colors = { dikirim: 'blue', dikirim_sebagian: 'blue', diterima: 'green' };
      return <StatusBadge status={r.status?.label} color={colors[r.status?.kode] || 'gray'} />;
    }},
  ];

  return (
    <div>
      <PageHeader title="Distribusi Outlet" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Operasional' }, { label: 'Distribusi' }]} actionLabel="Buat Distribusi" actionTo="/operasional/distribusi-outlet/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <Link to={`/operasional/distribusi-outlet/${row.id}`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Detail</Link>
        )}
      />
    </div>
  );
}

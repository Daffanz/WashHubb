import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getDistributions } from '../../api/distributions';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import StatusBadge from '../../components/ui/StatusBadge';

export default function DistributionList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getDistributions({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'Nomor Distribusi', render: (r) => <span className="font-mono font-medium text-wash-800">{r.nomor_distribusi}</span> },
    { label: 'Nomor PO', render: (r) => r.purchase_order?.nomor_po || '-' },
    { label: 'Status', render: (r) => <StatusBadge status={r.status} /> },
  ];

  return (
    <div>
      <PageHeader title="Distribusi Barang" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Procurement' }, { label: 'Distribusi' }]} actionLabel="Buat Distribusi" actionTo="/procurement/distributions/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => <Link to={`/procurement/distributions/${row.id}`} className="text-sm text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50">Detail</Link>}
      />
    </div>
  );
}

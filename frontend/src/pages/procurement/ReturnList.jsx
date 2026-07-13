import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import StatusBadge from '../../components/ui/StatusBadge';
import { formatDateTime } from '../../utils/format';
import { useAuth } from '../../hooks/useAuth';

export default function ReturnList() {
  const { user } = useAuth();
  const canCreate = user?.role?.kode === 'procurement' || user?.role?.kode === 'admin_it';
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try {
      const res = await fetch(`/api/procurement/returns?page=${p}`, { headers: { Authorization: `Bearer ${localStorage.getItem('washhub_token')}` } });
      const d = await res.json();
      setData(d.data); setMeta(d.meta);
    } catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'ID', render: (r) => <span className="font-medium">#{r.id}</span> },
    { label: 'PO', render: (r) => r.po?.nomor_po || '-' },
    { label: 'Supplier', render: (r) => r.po?.supplier || '-' },
    { label: 'Tanggal', render: (r) => formatDateTime(r.tanggal_retur) },
    { label: 'Status', render: (r) => <StatusBadge status={r.status?.label} color={r.status?.kode === 'selesai' ? 'green' : r.status?.kode === 'pengganti_dikirim' ? 'blue' : 'yellow'} /> },
    { label: 'Items', render: (r) => {
      const total = (r.detail_bahan_baku?.length || 0) + (r.detail_mesin?.length || 0);
      const selesai = (r.detail_bahan_baku?.filter(d => d.status?.kode === 'selesai').length || 0) + (r.detail_mesin?.filter(d => d.status?.kode === 'selesai').length || 0);
      return <span className="text-xs">{selesai}/{total}</span>;
    }},
  ];

  return (
    <div>
      <PageHeader title="Retur Barang" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Pengadaan' }, { label: 'Retur' }]}
        actionLabel={canCreate ? 'Buat Retur' : null} actionTo={canCreate ? '/procurement/returns/create' : null} />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <Link to={`/procurement/returns/${row.id}`} className="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50">Detail</Link>
        )}
      />
    </div>
  );
}

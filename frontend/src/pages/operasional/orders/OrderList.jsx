import React, { useState, useEffect, useCallback } from 'react';
import toast from 'react-hot-toast';
import { getOrders } from '../../../api/orderCucian';
import PageHeader from '../../../components/ui/PageHeader';
import DataTable from '../../../components/ui/DataTable';
import StatusBadge from '../../../components/ui/StatusBadge';
import { formatRupiah, formatDateTime } from '../../../utils/format';
import { Link } from 'react-router-dom';

export default function OrderList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getOrders({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'ID', render: (r) => <span className="font-mono text-sm">#{r.id}</span> },
    { label: 'Outlet', render: (r) => r.outlet?.nama || '-' },
    { label: 'Layanan', render: (r) => r.jenis_layanan?.nama || '-' },
    { label: 'Mesin', render: (r) => r.mesin?.nama || '-' },
    { label: 'Berat', render: (r) => `${r.berat} kg` },
    { label: 'Total', render: (r) => formatRupiah(r.total_harga) },
    { label: 'Status', render: (r) => <StatusBadge status={r.status?.label} color={r.status?.kode === 'selesai' ? 'green' : r.status?.kode === 'dibatalkan' ? 'red' : 'blue'} /> },
    { label: 'Waktu', render: (r) => formatDateTime(r.waktu_masuk) },
  ];

  return (
    <div>
      <PageHeader title="Order Cucian" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Operasional' }, { label: 'Orders' }]} actionLabel="Buat Order" actionTo="/operasional/orders/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <Link to={`/operasional/orders/${row.id}`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Detail</Link>
        )}
      />
    </div>
  );
}

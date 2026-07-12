import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import StatusBadge from '../../components/ui/StatusBadge';
import { formatRupiah, formatDate } from '../../utils/format';

export default function ReceiptList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await fetch(`/api/procurement/receipts?page=${p}`, { headers: { Authorization: `Bearer ${localStorage.getItem('washhub_token')}` } }); const d = await res.json(); setData(d.data); setMeta(d.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'Nomor', render: (r) => <span className="font-mono font-medium text-wash-800">{r.nomor_penerimaan}</span> },
    { label: 'Distribusi', render: (r) => r.distribusi_barang?.nomor_distribusi || '-' },
    { label: 'PO', render: (r) => r.distribusi_barang?.po?.nomor_po || '-' },
    { label: 'Tanggal', render: (r) => formatDate(r.tanggal_terima) },
    { label: 'Total', render: (r) => <span className="font-medium">{formatRupiah(r.total_bayar)}</span> },
    { label: 'Status', render: (r) => <StatusBadge status={r.status?.label} color={r.status?.kode === 'selesai' ? 'green' : r.status?.kode === 'menunggu_retur' ? 'amber' : 'gray'} /> },
  ];

  return (
    <div>
      <PageHeader title="Penerimaan Barang" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Procurement' }, { label: 'Penerimaan' }]} actionLabel="Catat Penerimaan" actionTo="/procurement/receipts/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => <Link to={`/procurement/receipts/${row.id}`} className="text-sm text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50">Detail</Link>}
      />
    </div>
  );
}

import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getReceipts } from '../../api/receipts';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import { formatRupiah, formatDate } from '../../utils/format';

export default function ReceiptList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getReceipts({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'Distribusi', render: (r) => r.distribusi_barang?.nomor_distribusi || '-' },
    { label: 'Tanggal Terima', render: (r) => formatDate(r.tanggal_terima) },
    { label: 'Total Bayar', render: (r) => <span className="font-medium">{formatRupiah(r.total_bayar)}</span> },
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

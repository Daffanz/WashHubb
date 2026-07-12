import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getReceipt } from '../../api/receipts';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatRupiah, formatDate } from '../../utils/format';

export default function ReceiptDetail() {
  const { id } = useParams();
  const [receipt, setReceipt] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getReceipt(id).then((res) => setReceipt(res.data.data)).catch(() => toast.error('Gagal memuat data')).finally(() => setLoading(false));
  }, [id]);

  if (loading) return <LoadingSpinner />;
  if (!receipt) return null;

  return (
    <div>
      <PageHeader title="Detail Penerimaan" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Penerimaan', to: '/procurement/receipts' }, { label: 'Detail' }]} />
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-lg">
        <dl className="space-y-3 text-sm">
          <div className="flex justify-between"><dt className="text-gray-500">Nomor Distribusi</dt><dd className="font-mono font-medium">{receipt.distribusi_barang?.nomor_distribusi || '-'}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Nomor PO</dt><dd>{receipt.distribusi_barang?.purchase_order?.nomor_po || '-'}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Tanggal Terima</dt><dd>{formatDate(receipt.tanggal_terima)}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Total Bayar</dt><dd className="font-semibold text-wash-800">{formatRupiah(receipt.total_bayar)}</dd></div>
          {receipt.catatan && <div className="flex justify-between"><dt className="text-gray-500">Catatan</dt><dd>{receipt.catatan}</dd></div>}
        </dl>
      </div>
    </div>
  );
}

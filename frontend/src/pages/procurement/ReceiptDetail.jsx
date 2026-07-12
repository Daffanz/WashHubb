import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getReceipt } from '../../api/receipts';
import PageHeader from '../../components/ui/PageHeader';
import StatusBadge from '../../components/ui/StatusBadge';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatRupiah, formatDate, formatQty } from '../../utils/format';

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
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Penerimaan</h3>
          <dl className="space-y-3 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Nomor</dt><dd className="font-mono font-medium">{receipt.nomor_penerimaan}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Distribusi</dt><dd>{receipt.distribusi_barang?.nomor_distribusi || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">PO</dt><dd>{receipt.distribusi_barang?.po?.nomor_po || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Tanggal</dt><dd>{formatDate(receipt.tanggal_terima)}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={receipt.status?.label} color={receipt.status?.kode === 'selesai' ? 'green' : receipt.status?.kode === 'menunggu_retur' ? 'amber' : 'gray'} /></dd></div>
            <div className="flex justify-between pt-2 border-t"><dt className="font-semibold">Total</dt><dd className="font-bold">{formatRupiah(receipt.total_bayar)}</dd></div>
          </dl>
          {receipt.retur && (
            <div className="mt-4 pt-4 border-t">
              <p className="text-xs text-gray-500">Retur: <Link to={`/procurement/returns/${receipt.retur.id}`} className="text-blue-600 hover:text-blue-800 font-medium">{receipt.retur.status}</Link></p>
            </div>
          )}
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Items</h3>
          {receipt.items_bahan_baku?.length > 0 && receipt.items_bahan_baku.map((item, i) => (
            <div key={i} className={`flex items-center gap-3 mb-2 p-3 rounded-lg ${item.kondisi === 'baik' ? 'bg-emerald-50' : 'bg-amber-50 border border-amber-200'}`}>
              <span className="flex-1 text-sm font-medium">BB #{i + 1}</span>
              <span className="text-sm">Diterima: {formatQty(item.qty_diterima)}</span>
              <span className={`text-xs font-medium px-2 py-0.5 rounded ${item.kondisi === 'baik' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}`}>{item.kondisi}</span>
            </div>
          ))}
          {receipt.items_mesin?.length > 0 && receipt.items_mesin.map((item, i) => (
            <div key={i} className={`flex items-center gap-3 mb-2 p-3 rounded-lg ${item.kondisi === 'baik' ? 'bg-emerald-50' : 'bg-amber-50 border border-amber-200'}`}>
              <span className="flex-1 text-sm font-medium">Mesin #{i + 1}</span>
              {item.nomor_seri && <span className="text-xs text-gray-400">SN: {item.nomor_seri}</span>}
              <span className="text-sm">Diterima: {item.qty_diterima}</span>
              <span className={`text-xs font-medium px-2 py-0.5 rounded ${item.kondisi === 'baik' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}`}>{item.kondisi}</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

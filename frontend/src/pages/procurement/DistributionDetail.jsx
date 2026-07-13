import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getDistribution, diterimaDistribution } from '../../api/distributions';
import PageHeader from '../../components/ui/PageHeader';
import StatusBadge from '../../components/ui/StatusBadge';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatQty } from '../../utils/format';

export default function DistributionDetail() {
  const { id } = useParams();
  const [dist, setDist] = useState(null);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState(false);

  const refresh = async () => { const res = await getDistribution(id); setDist(res.data.data); };

  useEffect(() => { refresh().catch(() => toast.error('Gagal memuat data')).finally(() => setLoading(false)); }, [id]);

  const handleDiterima = async () => {
    if (!confirm('Tandai distribusi diterima?')) return;
    setProcessing(true);
    try { await diterimaDistribution(id); toast.success('Distribusi ditandai diterima.'); await refresh(); }
    catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setProcessing(false);
  };

  if (loading) return <LoadingSpinner />;
  if (!dist) return null;

  const sk = dist.status?.kode;

  return (
    <div>
      <PageHeader title={`Distribusi: ${dist.nomor_distribusi}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Distribusi', to: '/procurement/distributions' }, { label: 'Detail' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Informasi</h3>
          <dl className="space-y-3 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Nomor Distribusi</dt><dd className="font-mono font-medium">{dist.nomor_distribusi}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Nomor PO</dt><dd>{dist.po?.nomor_po || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={dist.status?.label} color={sk === 'diterima' ? 'green' : 'blue'} /></dd></div>
          </dl>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Aksi</h3>
          {(sk === 'dikirim' || sk === 'dikirim_sebagian') ? (
            <button onClick={handleDiterima} disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">Tandai Diterima</button>
          ) : sk === 'diterima' ? <p className="text-sm text-emerald-600">Sudah diterima.</p> : null}
        </div>
      </div>
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h3 className="text-sm font-semibold text-gray-900 mb-4">Items</h3>
        {dist.items_bahan_baku?.length > 0 && (
          <table className="min-w-full text-sm mb-4">
            <thead><tr className="border-b"><th className="text-left py-2 text-gray-500">Item</th><th className="text-right py-2 text-gray-500">Qty Disetujui</th><th className="text-right py-2 text-gray-500">Dikirim</th></tr></thead>
            <tbody>{dist.items_bahan_baku.map(i => <tr key={i.id} className="border-b last:border-0"><td className="py-2 font-medium">{i.nama}</td><td className="py-2 text-right text-gray-500">{formatQty(i.qty_disetujui)}</td><td className="py-2 text-right font-semibold">{formatQty(i.jumlah_kirim)}</td></tr>)}</tbody>
          </table>
        )}
        {dist.items_mesin?.length > 0 && (
          <table className="min-w-full text-sm">
            <thead><tr className="border-b"><th className="text-left py-2 text-gray-500">Item</th><th className="text-right py-2 text-gray-500">Qty Disetujui</th><th className="text-right py-2 text-gray-500">Dikirim</th></tr></thead>
            <tbody>{dist.items_mesin.map(i => <tr key={i.id} className="border-b last:border-0"><td className="py-2 font-medium">{i.nama}</td><td className="py-2 text-right text-gray-500">{formatQty(i.qty_disetujui)}</td><td className="py-2 text-right font-semibold">{formatQty(i.jumlah_kirim)}</td></tr>)}</tbody>
          </table>
        )}
      </div>
    </div>
  );
}

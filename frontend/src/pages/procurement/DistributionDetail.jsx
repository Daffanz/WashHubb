import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getDistribution } from '../../api/distributions';
import PageHeader from '../../components/ui/PageHeader';
import StatusBadge from '../../components/ui/StatusBadge';
import LoadingSpinner from '../../components/ui/LoadingSpinner';

export default function DistributionDetail() {
  const { id } = useParams();
  const [dist, setDist] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getDistribution(id).then((res) => setDist(res.data.data)).catch(() => toast.error('Gagal memuat data')).finally(() => setLoading(false));
  }, [id]);

  if (loading) return <LoadingSpinner />;
  if (!dist) return null;

  return (
    <div>
      <PageHeader title={`Distribusi: ${dist.nomor_distribusi}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Distribusi', to: '/procurement/distributions' }, { label: 'Detail' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Informasi Distribusi</h3>
          <dl className="space-y-3 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Nomor Distribusi</dt><dd className="font-mono font-medium">{dist.nomor_distribusi}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Nomor PO</dt><dd>{dist.purchase_order?.nomor_po || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={dist.status} /></dd></div>
            {dist.catatan && <div className="flex justify-between"><dt className="text-gray-500">Catatan</dt><dd>{dist.catatan}</dd></div>}
          </dl>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Penerimaan Barang</h3>
          {dist.penerimaan_barangs?.length > 0 ? (
            <table className="min-w-full text-sm">
              <thead><tr className="border-b"><th className="text-left py-2 text-gray-500">Tanggal</th><th className="text-right py-2 text-gray-500">Total Bayar</th></tr></thead>
              <tbody>
                {dist.penerimaan_barangs.map((p) => (
                  <tr key={p.id} className="border-b last:border-0">
                    <td className="py-2">{p.tanggal_terima}</td>
                    <td className="py-2 text-right font-medium">{new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p.total_bayar)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          ) : <p className="text-sm text-gray-400">Belum ada penerimaan</p>}
        </div>
      </div>
    </div>
  );
}

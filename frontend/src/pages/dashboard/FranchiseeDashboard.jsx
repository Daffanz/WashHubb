import React, { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { getDashboardFranchisee } from '../../api/dashboard';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatRupiah } from '../../utils/format';

export default function FranchiseeDashboard() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getDashboardFranchisee().then((res) => setData(res.data.data || [])).catch(() => toast.error('Gagal memuat data')).finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingSpinner />;

  return (
    <div>
      <PageHeader title="Dashboard Franchisee" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Dashboard' }]} />
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">Total Outlet</p>
          <p className="text-2xl font-bold text-gray-900">{data.length}</p>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">Total Omset</p>
          <p className="text-2xl font-bold text-emerald-600">{formatRupiah(data.reduce((a, o) => a + (o.omset || 0), 0))}</p>
        </div>
      </div>
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h3 className="text-sm font-semibold text-gray-900 mb-4">Ringkasan Outlet</h3>
        <div className="space-y-4">
          {data.map((o, i) => (
            <div key={i} className="p-4 bg-gray-50 rounded-lg">
              <div className="flex items-center justify-between mb-2">
                <span className="font-medium">{o.outlet?.nama || '-'}</span>
                <span className="font-semibold text-emerald-600">{formatRupiah(o.omset)}</span>
              </div>
              {o.loyalti && (
                <div className="flex items-center gap-3 text-sm">
                  <span className="text-gray-500">Bonus {o.loyalti.periode}:</span>
                  <span className="font-medium">{o.loyalti.jumlah_bonus ? formatRupiah(o.loyalti.jumlah_bonus) : '-'}</span>
                  {o.loyalti.menunggu_konfirmasi && <span className="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded">Menunggu Konfirmasi</span>}
                </div>
              )}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

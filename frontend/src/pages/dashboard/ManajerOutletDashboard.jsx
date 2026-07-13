import React, { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { getDashboardManajerOutlet } from '../../api/dashboard';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';

export default function ManajerOutletDashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getDashboardManajerOutlet().then((res) => setData(res.data.data)).catch(() => toast.error('Gagal memuat data')).finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingSpinner />;
  if (!data) return <div><PageHeader title="Dashboard Manajer Outlet" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Dashboard' }]} /><p className="text-sm text-gray-500">Tidak ada data outlet</p></div>;

  return (
    <div>
      <PageHeader title="Dashboard Manajer Outlet" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Dashboard' }]} />
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">Order Aktif</p>
          <p className="text-2xl font-bold text-blue-600">{data.order_aktif || 0}</p>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">Service Menunggu</p>
          <p className="text-2xl font-bold text-amber-600">{data.service_menunggu_persetujuan || 0}</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Stok Kritis</h3>
          {(data.stok_kritis || []).length === 0 ? (
            <p className="text-sm text-gray-500">Tidak ada stok kritis</p>
          ) : (
            <div className="space-y-2">
              {data.stok_kritis.map((s, i) => (
                <div key={i} className="flex items-center justify-between p-2 bg-red-50 rounded">
                  <span className="text-sm font-medium">{s.item || '-'}</span>
                  <span className="text-sm text-red-600">{s.stok_saat_ini} / {s.stok_minimum}</span>
                </div>
              ))}
            </div>
          )}
        </div>

        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Mesin Bermasalah</h3>
          {(data.mesin_bermasalah || []).length === 0 ? (
            <p className="text-sm text-gray-500">Tidak ada mesin bermasalah</p>
          ) : (
            <div className="space-y-2">
              {data.mesin_bermasalah.map((m, i) => (
                <div key={i} className="flex items-center justify-between p-2 bg-amber-50 rounded">
                  <div>
                    <span className="text-sm font-medium">{m.mesin || '-'}</span>
                    {m.nomor_seri && <span className="text-xs text-gray-400 ml-2">({m.nomor_seri})</span>}
                  </div>
                  <span className={`text-xs font-medium px-2 py-0.5 rounded ${m.status === 'maintenance' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'}`}>{m.status}</span>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

import React, { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { getDashboardFranchisor } from '../../api/dashboard';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatRupiah } from '../../utils/format';

export default function FranchisorDashboard() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getDashboardFranchisor().then((res) => setData(res.data.data || [])).catch(() => toast.error('Gagal memuat data')).finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingSpinner />;

  return (
    <div>
      <PageHeader title="Dashboard Franchisor" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Dashboard' }]} />
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">Total Outlet</p>
          <p className="text-2xl font-bold text-gray-900">{data.length}</p>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">Total Omset</p>
          <p className="text-2xl font-bold text-emerald-600">{formatRupiah(data.reduce((a, o) => a + (o.omset || 0), 0))}</p>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">Mesin Bermasalah</p>
          <p className="text-2xl font-bold text-red-600">{data.reduce((a, o) => a + (o.mesin_bermasalah || 0), 0)}</p>
        </div>
      </div>
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h3 className="text-sm font-semibold text-gray-900 mb-4">Ringkasan Outlet</h3>
        <div className="overflow-x-auto">
          <table className="min-w-full text-sm">
            <thead><tr className="border-b">
              <th className="text-left py-2 text-gray-500">Outlet</th>
              <th className="text-left py-2 text-gray-500">Franchise</th>
              <th className="text-right py-2 text-gray-500">Omset</th>
              <th className="text-right py-2 text-gray-500">Order Aktif</th>
              <th className="text-right py-2 text-gray-500">Mesin Bermasalah</th>
              <th className="text-center py-2 text-gray-500">Loyalti</th>
            </tr></thead>
            <tbody>
              {data.map((o, i) => (
                <tr key={i} className="border-b last:border-0">
                  <td className="py-2 font-medium">{o.outlet?.nama || '-'}</td>
                  <td className="py-2 text-gray-500">{o.franchise || '-'}</td>
                  <td className="py-2 text-right font-semibold">{formatRupiah(o.omset)}</td>
                  <td className="py-2 text-right">{o.order_aktif}</td>
                  <td className="py-2 text-right">{o.mesin_bermasalah > 0 ? <span className="text-red-600 font-medium">{o.mesin_bermasalah}</span> : '0'}</td>
                  <td className="py-2 text-center">{o.loyalti?.status || '-'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

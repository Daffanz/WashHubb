import React, { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { getDashboardPengadaan } from '../../api/dashboard';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';

export default function PengadaanDashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getDashboardPengadaan().then((res) => setData(res.data.data)).catch(() => toast.error('Gagal memuat data')).finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingSpinner />;
  if (!data) return null;

  return (
    <div>
      <PageHeader title="Dashboard Pengadaan" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Dashboard' }]} />
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">PO Menunggu</p>
          <p className="text-2xl font-bold text-amber-600">{data.po_menunggu || 0}</p>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">Permintaan Outlet Menunggu</p>
          <p className="text-2xl font-bold text-amber-600">{data.permintaan_outlet_menunggu || 0}</p>
        </div>
      </div>
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Stok Pusat Bahan Baku</h3>
          <div className="space-y-2">
            {(data.stok_pusat_bahan_baku || []).map((s, i) => (
              <div key={i} className={`flex items-center justify-between p-2 rounded ${s.kritis ? 'bg-red-50' : 'bg-gray-50'}`}>
                <span className="text-sm">{s.item || '-'}</span>
                <span className={`text-sm font-medium ${s.kritis ? 'text-red-600' : 'text-gray-900'}`}>{s.stok_saat_ini}</span>
              </div>
            ))}
          </div>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Stok Pusat Mesin</h3>
          <div className="space-y-2">
            {(data.stok_pusat_mesin || []).map((s, i) => (
              <div key={i} className={`flex items-center justify-between p-2 rounded ${s.kritis ? 'bg-red-50' : 'bg-gray-50'}`}>
                <span className="text-sm">{s.item || '-'}</span>
                <span className={`text-sm font-medium ${s.kritis ? 'text-red-600' : 'text-gray-900'}`}>{s.stok_saat_ini}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

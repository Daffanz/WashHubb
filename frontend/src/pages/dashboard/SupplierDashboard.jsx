import React, { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { getDashboardSupplier } from '../../api/dashboard';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';

export default function SupplierDashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getDashboardSupplier().then((res) => setData(res.data.data)).catch(() => toast.error('Gagal memuat data')).finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingSpinner />;
  if (!data) return null;

  return (
    <div>
      <PageHeader title="Dashboard Supplier" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Dashboard' }]} />
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <p className="text-sm text-gray-500 mb-1">PO Aktif</p>
        <p className="text-2xl font-bold text-amber-600">{data.po_aktif || 0}</p>
      </div>
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h3 className="text-sm font-semibold text-gray-900 mb-4">Stok Supplier</h3>
        <div className="space-y-2">
          {(data.stok_supplier || []).map((s, i) => (
            <div key={i} className="flex items-center justify-between p-2 bg-gray-50 rounded">
              <span className="text-sm">{s.item || '-'}</span>
              <span className="text-sm font-medium">{s.stok_saat_ini}</span>
            </div>
          ))}
          {(!data.stok_supplier || data.stok_supplier.length === 0) && <p className="text-sm text-gray-500">Tidak ada data stok</p>}
        </div>
      </div>
    </div>
  );
}

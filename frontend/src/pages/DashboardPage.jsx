import React from 'react';
import { useAuth } from '../hooks/useAuth.js';
import PageHeader from '../components/ui/PageHeader';
import FranchisorDashboard from './dashboard/FranchisorDashboard';
import PengadaanDashboard from './dashboard/PengadaanDashboard';
import SupplierDashboard from './dashboard/SupplierDashboard';
import FranchiseeDashboard from './dashboard/FranchiseeDashboard';
import ManajerOutletDashboard from './dashboard/ManajerOutletDashboard';

const dashboardMap = {
  franchisor: FranchisorDashboard,
  franchise: FranchiseeDashboard,
  procurement: PengadaanDashboard,
  supplier: SupplierDashboard,
  franchisee: FranchiseeDashboard,
  manager_outlet: ManajerOutletDashboard,
};

export default function DashboardPage() {
  const { user } = useAuth();
  const roleKode = user?.role?.kode;
  const RoleDashboard = dashboardMap[roleKode];

  // Jika ada dashboard khusus untuk role ini, render dashboard tersebut
  if (RoleDashboard) {
    return <RoleDashboard />;
  }

  // Default dashboard untuk admin_it dan role lainnya
  return (
    <div>
      <PageHeader title="Dashboard" breadcrumbs={[{ label: 'Home' }, { label: 'Dashboard' }]} />

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">Nama</p>
          <p className="text-lg font-semibold text-gray-900">{user?.nama || '-'}</p>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">Role</p>
          <p className="text-lg font-semibold text-gray-900">{user?.role?.label || '-'}</p>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <p className="text-sm text-gray-500 mb-1">Email</p>
          <p className="text-lg font-semibold text-gray-900">{user?.email || '-'}</p>
        </div>
      </div>

      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h3 className="text-sm font-semibold text-gray-900 mb-2">Selamat Datang di WashHub</h3>
        <p className="text-sm text-gray-500">Sistem Manajemen Franchise Laundry. Gunakan menu di samping untuk mengelola data.</p>
      </div>
    </div>
  );
}

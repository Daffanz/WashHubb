import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { useAuth } from './hooks/useAuth.js';
import MainLayout from './components/layout/MainLayout';
import LoginPage from './pages/auth/LoginPage';
import ForgotPasswordPage from './pages/auth/ForgotPasswordPage';
import DashboardPage from './pages/DashboardPage';

// Module 1
import UserList from './pages/users/UserList';
import UserForm from './pages/users/UserForm';
import RoleList from './pages/roles/RoleList';
import RoleForm from './pages/roles/RoleForm';
import SupplierList from './pages/suppliers/SupplierList';
import SupplierForm from './pages/suppliers/SupplierForm';
import SupplierStockPage from './pages/suppliers/SupplierStockPage';
import SupplierStockDetail from './pages/suppliers/SupplierStockDetail';

// Module 2
import CategoryList from './pages/master/categories/CategoryList';
import CategoryForm from './pages/master/categories/CategoryForm';
import MaterialList from './pages/master/materials/MaterialList';
import MaterialForm from './pages/master/materials/MaterialForm';
import ServiceList from './pages/master/services/ServiceList';
import ServiceForm from './pages/master/services/ServiceForm';
import ServiceDetail from './pages/master/services/ServiceDetail';
import MachineList from './pages/master/machines/MachineList';
import MachineForm from './pages/master/machines/MachineForm';

// Module 3
import PurchaseOrderList from './pages/procurement/PurchaseOrderList';
import PurchaseOrderForm from './pages/procurement/PurchaseOrderForm';
import PurchaseOrderDetail from './pages/procurement/PurchaseOrderDetail';
import DistributionList from './pages/procurement/DistributionList';
import DistributionForm from './pages/procurement/DistributionForm';
import DistributionDetail from './pages/procurement/DistributionDetail';
import ReceiptList from './pages/procurement/ReceiptList';
import ReceiptForm from './pages/procurement/ReceiptForm';
import ReceiptDetail from './pages/procurement/ReceiptDetail';
import ReturnList from './pages/procurement/ReturnList';
import ReturnForm from './pages/procurement/ReturnForm';
import ReturnDetail from './pages/procurement/ReturnDetail';

// Module 4
import StockList from './pages/inventory/StockList';
import StockDetail from './pages/inventory/StockDetail';
import MutationList from './pages/inventory/MutationList';
import MutationForm from './pages/inventory/MutationForm';

// Module 5 — Operasional
import OrderList from './pages/operasional/orders/OrderList';
import OrderForm from './pages/operasional/orders/OrderForm';
import OrderDetail from './pages/operasional/orders/OrderDetail';
import PermintaanList from './pages/operasional/permintaan/PermintaanList';
import PermintaanForm from './pages/operasional/permintaan/PermintaanForm';
import PermintaanDetail from './pages/operasional/permintaan/PermintaanDetail';
import DistribusiOutletList from './pages/operasional/distribusi/DistribusiList';
import DistribusiOutletForm from './pages/operasional/distribusi/DistribusiForm';
import DistribusiOutletDetail from './pages/operasional/distribusi/DistribusiDetail';
import JadwalServiceList from './pages/operasional/service/ServiceList';
import JadwalServiceForm from './pages/operasional/service/ServiceForm';
import JadwalServiceDetail from './pages/operasional/service/ServiceDetail';
import ShiftList from './pages/operasional/shift/ShiftList';
import ShiftForm from './pages/operasional/shift/ShiftForm';
import ShiftDetail from './pages/operasional/shift/ShiftDetail';

// Module 6 — Franchise
import OutletList from './pages/franchise/outlets/OutletList';
import OutletForm from './pages/franchise/outlets/OutletForm';
import OutletDetail from './pages/franchise/outlets/OutletDetail';
import LoyaltiList from './pages/franchise/loyalti/LoyaltiList';
import LoyaltiForm from './pages/franchise/loyalti/LoyaltiForm';
import LoyaltiDetail from './pages/franchise/loyalti/LoyaltiDetail';

// Module 7 — Dashboard
import FranchisorDashboard from './pages/dashboard/FranchisorDashboard';
import PengadaanDashboard from './pages/dashboard/PengadaanDashboard';
import SupplierDashboard from './pages/dashboard/SupplierDashboard';
import FranchiseeDashboard from './pages/dashboard/FranchiseeDashboard';
import ManajerOutletDashboard from './pages/dashboard/ManajerOutletDashboard';

function ProtectedRoute({ children }) {
  const { user, loading } = useAuth();
  if (loading) return <div className="flex items-center justify-center h-screen"><div className="animate-spin rounded-full h-12 w-12 border-b-2 border-wash-900"></div></div>;
  if (!user) return <Navigate to="/login" replace />;
  return children;
}

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/forgot-password" element={<ForgotPasswordPage />} />
      <Route path="/" element={<ProtectedRoute><MainLayout /></ProtectedRoute>}>
        <Route index element={<Navigate to="/dashboard" replace />} />
        <Route path="dashboard" element={<DashboardPage />} />
        {/* Module 1 */}
        <Route path="users" element={<UserList />} />
        <Route path="users/create" element={<UserForm />} />
        <Route path="users/:id/edit" element={<UserForm />} />
        <Route path="roles" element={<RoleList />} />
        <Route path="roles/create" element={<RoleForm />} />
        <Route path="roles/:id/edit" element={<RoleForm />} />
        <Route path="suppliers" element={<SupplierList />} />
        <Route path="suppliers/create" element={<SupplierForm />} />
        <Route path="suppliers/:id/edit" element={<SupplierForm />} />
        <Route path="suppliers/stock" element={<SupplierStockPage />} />
        <Route path="suppliers/stock/:id" element={<SupplierStockDetail />} />
        {/* Module 2 */}
        <Route path="master/categories" element={<CategoryList />} />
        <Route path="master/categories/create" element={<CategoryForm />} />
        <Route path="master/categories/:id/edit" element={<CategoryForm />} />
        <Route path="master/materials" element={<MaterialList />} />
        <Route path="master/materials/create" element={<MaterialForm />} />
        <Route path="master/materials/:id/edit" element={<MaterialForm />} />
        <Route path="master/services" element={<ServiceList />} />
        <Route path="master/services/create" element={<ServiceForm />} />
        <Route path="master/services/:id/edit" element={<ServiceForm />} />
        <Route path="master/services/:id" element={<ServiceDetail />} />
        <Route path="master/machines" element={<MachineList />} />
        <Route path="master/machines/create" element={<MachineForm />} />
        <Route path="master/machines/:id/edit" element={<MachineForm />} />
        {/* Module 3 */}
        <Route path="procurement/purchase-orders" element={<PurchaseOrderList />} />
        <Route path="procurement/purchase-orders/create" element={<PurchaseOrderForm />} />
        <Route path="procurement/purchase-orders/:id/edit" element={<PurchaseOrderForm />} />
        <Route path="procurement/purchase-orders/:id" element={<PurchaseOrderDetail />} />
        <Route path="procurement/distributions" element={<DistributionList />} />
        <Route path="procurement/distributions/create" element={<DistributionForm />} />
        <Route path="procurement/distributions/:id" element={<DistributionDetail />} />
        <Route path="procurement/receipts" element={<ReceiptList />} />
        <Route path="procurement/receipts/create" element={<ReceiptForm />} />
        <Route path="procurement/receipts/:id" element={<ReceiptDetail />} />
        <Route path="procurement/returns" element={<ReturnList />} />
        <Route path="procurement/returns/create" element={<ReturnForm />} />
        <Route path="procurement/returns/:id" element={<ReturnDetail />} />
        {/* Module 4 */}
        <Route path="inventory/stocks" element={<StockList />} />
        <Route path="inventory/stocks/:type/:id" element={<StockDetail />} />
        <Route path="inventory/mutations" element={<MutationList />} />
        <Route path="inventory/mutations/create" element={<MutationForm />} />
        {/* Module 5 — Operasional */}
        <Route path="operasional/orders" element={<OrderList />} />
        <Route path="operasional/orders/create" element={<OrderForm />} />
        <Route path="operasional/orders/:id" element={<OrderDetail />} />
        <Route path="operasional/permintaan-stok" element={<PermintaanList />} />
        <Route path="operasional/permintaan-stok/create" element={<PermintaanForm />} />
        <Route path="operasional/permintaan-stok/:id" element={<PermintaanDetail />} />
        <Route path="operasional/distribusi-outlet" element={<DistribusiOutletList />} />
        <Route path="operasional/distribusi-outlet/create" element={<DistribusiOutletForm />} />
        <Route path="operasional/distribusi-outlet/:id" element={<DistribusiOutletDetail />} />
        <Route path="operasional/jadwal-service" element={<JadwalServiceList />} />
        <Route path="operasional/jadwal-service/create" element={<JadwalServiceForm />} />
        <Route path="operasional/jadwal-service/:id" element={<JadwalServiceDetail />} />
        <Route path="operasional/jadwal-shift" element={<ShiftList />} />
        <Route path="operasional/jadwal-shift/create" element={<ShiftForm />} />
        <Route path="operasional/jadwal-shift/:id/edit" element={<ShiftForm />} />
        <Route path="operasional/jadwal-shift/:id" element={<ShiftDetail />} />
        {/* Module 6 — Franchise */}
        <Route path="franchise/outlets" element={<OutletList />} />
        <Route path="franchise/outlets/create" element={<OutletForm />} />
        <Route path="franchise/outlets/:id/edit" element={<OutletForm />} />
        <Route path="franchise/outlets/:id" element={<OutletDetail />} />
        <Route path="franchise/loyalti" element={<LoyaltiList />} />
        <Route path="franchise/loyalti/create" element={<LoyaltiForm />} />
        <Route path="franchise/loyalti/:id" element={<LoyaltiDetail />} />
        {/* Module 7 — Dashboard */}
        <Route path="dashboard/franchisor" element={<FranchisorDashboard />} />
        <Route path="dashboard/pengadaan" element={<PengadaanDashboard />} />
        <Route path="dashboard/supplier" element={<SupplierDashboard />} />
        <Route path="dashboard/franchisee" element={<FranchiseeDashboard />} />
        <Route path="dashboard/manajer-outlet" element={<ManajerOutletDashboard />} />
      </Route>
    </Routes>
  );
}

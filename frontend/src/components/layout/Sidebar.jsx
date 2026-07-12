import React from 'react';
import { NavLink } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth.js';

const menuItems = [
  { label: 'Dashboard', to: '/dashboard', permission: 'user-list', group: 'Umum' },
  { label: 'Users', to: '/users', permission: 'user-list', group: 'Akun' },
  { label: 'Roles', to: '/roles', permission: 'role-list', group: 'Akun' },
  { label: 'Suppliers', to: '/suppliers', permission: 'supplier-list', group: 'Akun' },
  { label: 'Stok Supplier', to: '/suppliers/stock', permission: 'supplier-stock-manage', group: 'Stok Supplier' },
  { label: 'Categories', to: '/master/categories', permission: 'category-list', group: 'Data Master' },
  { label: 'Materials', to: '/master/materials', permission: 'material-list', group: 'Data Master' },
  { label: 'Services', to: '/master/services', permission: 'service-list', group: 'Data Master' },
  { label: 'Machines', to: '/master/machines', permission: 'machine-list', group: 'Data Master' },
  { label: 'Purchase Orders', to: '/procurement/purchase-orders', permission: 'po-list', group: 'Pengadaan' },
  { label: 'Distributions', to: '/procurement/distributions', permission: 'distribution-list', group: 'Pengadaan' },
  { label: 'Receipts', to: '/procurement/receipts', permission: 'receipt-list', group: 'Pengadaan' },
  { label: 'Returns', to: '/procurement/returns', permission: 'retur-list', group: 'Pengadaan' },
  { label: 'Stok Perusahaan', to: '/inventory/stocks', permission: 'stock-list', group: 'Inventory' },
  { label: 'Mutasi Barang', to: '/inventory/mutations', permission: 'mutation-list', group: 'Inventory' },
];

const iconMap = {
  Dashboard: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zm0 9.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zm0 9.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>,
  Users: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>,
  Roles: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>,
  Suppliers: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0H21a.75.75 0 00.75-.75V11.25a3 3 0 00-3-3h-1.5l-1.72-4.575A1.5 1.5 0 0014.87 2.25H9.13a1.5 1.5 0 00-1.43 1.05L6 7.5H3.75a3 3 0 00-3 3v7.125c0 .621.504 1.125 1.125 1.125h14.25" /></svg>,
  Categories: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M6 6h.008v.008H6V6z" /></svg>,
  Materials: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25" /></svg>,
  Services: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M11.42 15.17l-5.384 3.09a.75.75 0 01-1.06-.78l.94-6.02a.75.75 0 00-.27-.79L2.25 8.25m9.17 6.92l5.384 3.09a.75.75 0 001.06-.78l-.94-6.02a.75.75 0 00.27-.79l2.18-3.13" /></svg>,
  Machines: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>,
  'Purchase Orders': <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>,
  Distributions: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0H21a.75.75 0 00.75-.75V11.25a3 3 0 00-3-3h-1.5l-1.72-4.575A1.5 1.5 0 0014.87 2.25H9.13a1.5 1.5 0 00-1.43 1.05L6 7.5H3.75a3 3 0 00-3 3v7.125c0 .621.504 1.125 1.125 1.125h14.25" /></svg>,
  Receipts: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>,
  Returns: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>,
  Stocks: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>,
  Mutations: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>,
};

export default function Sidebar({ onClose }) {
  const { user } = useAuth();
  const perms = user?.permissions || [];

  const visibleItems = menuItems.filter((item) => perms.includes(item.permission));

  const groups = {};
  visibleItems.forEach((item) => {
    if (!groups[item.group]) groups[item.group] = [];
    groups[item.group].push(item);
  });

  return (
    <div className="flex flex-col h-full bg-wash-900 text-white">
      <div className="flex items-center gap-3 px-6 py-5 border-b border-wash-800">
        <div className="w-9 h-9 bg-white rounded-lg flex items-center justify-center">
          <svg className="w-6 h-6 text-wash-900" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 2.38 1.19 4.47 3 5.74V17a1 1 0 001 1h6a1 1 0 001-1v-2.26c1.81-1.27 3-3.36 3-5.74 0-3.87-3.13-7-7-7zm2 14h-4v-1h4v1zm0-2h-4v-1h4v1zm1.15-4.95L14 11.12V15h-4v-3.88l-1.15-1.07A4.993 4.993 0 017 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.63-.8 3.16-2.15 4.05z" />
          </svg>
        </div>
        <span className="text-lg font-bold tracking-tight">WashHub</span>
        <button onClick={onClose} className="ml-auto lg:hidden p-1 rounded-md hover:bg-wash-800 transition">
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>

      <nav className="flex-1 overflow-y-auto py-4 px-3 sidebar-scroll">
        {Object.entries(groups).map(([groupName, items]) => (
          <div key={groupName} className="mb-4">
            <p className="px-3 mb-2 text-xs font-semibold text-wash-400 uppercase tracking-wider">{groupName}</p>
            {items.map((item) => (
              <NavLink
                key={item.to}
                to={item.to}
                end={item.to.split('/').length === 2 && !item.to.includes('master') && !item.to.includes('procurement') && !item.to.includes('inventory')}
                onClick={onClose}
                className={({ isActive }) =>
                  `flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition mb-0.5 ${isActive ? 'bg-white/15 text-white shadow-sm' : 'text-wash-200 hover:bg-white/10 hover:text-white'}`
                }
              >
                {iconMap[item.label]}
                {item.label}
              </NavLink>
            ))}
          </div>
        ))}
      </nav>

      <div className="px-4 py-4 border-t border-wash-800">
        <div className="flex items-center gap-3">
          <div className="w-9 h-9 rounded-full bg-wash-700 flex items-center justify-center text-sm font-bold text-white">
            {user?.nama?.charAt(0)?.toUpperCase() || 'U'}
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-medium text-white truncate">{user?.nama || 'User'}</p>
            <p className="text-xs text-wash-400">{user?.role?.label || 'role'}</p>
          </div>
        </div>
      </div>
    </div>
  );
}

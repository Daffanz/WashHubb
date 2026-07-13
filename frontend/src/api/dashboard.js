import api from './axios';

export const getDashboardFranchisor = () => api.get('/dashboard/franchisor');
export const getDashboardPengadaan = () => api.get('/dashboard/pengadaan');
export const getDashboardSupplier = () => api.get('/dashboard/supplier');
export const getDashboardFranchisee = () => api.get('/dashboard/franchisee');
export const getDashboardManajerOutlet = () => api.get('/dashboard/manajer-outlet');

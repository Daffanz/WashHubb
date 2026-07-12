import api from './axios';

export const getDistributions = (params) => api.get('/procurement/distributions', { params });
export const getDistribution = (id) => api.get(`/procurement/distributions/${id}`);
// UC-35: Supplier buat distribusi
export const createDistribution = (data) => api.post('/procurement/distributions', data);
export const diterimaDistribution = (id) => api.patch(`/procurement/distributions/${id}/diterima`);

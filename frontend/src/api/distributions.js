import api from './axios';
export const getDistributions = (params) => api.get('/procurement/distributions', { params });
export const getDistribution = (id) => api.get(`/procurement/distributions/${id}`);
export const createDistribution = (data) => api.post('/procurement/distributions', data);

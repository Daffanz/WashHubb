import api from './axios';

export const getDistribusis = (params) => api.get('/operasional/distribusi-outlet', { params });
export const getDistribusi = (id) => api.get(`/operasional/distribusi-outlet/${id}`);
export const createDistribusi = (data) => api.post('/operasional/distribusi-outlet', data);
export const terimaDistribusi = (id, data) => api.patch(`/operasional/distribusi-outlet/${id}/terima`, data);

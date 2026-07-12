import api from './axios';

export const getReturns = (params) => api.get('/procurement/returns', { params });
export const getReturn = (id) => api.get(`/procurement/returns/${id}`);
export const createReturn = (data) => api.post('/procurement/returns', data);
export const kirimPenggantiReturn = (id, data) => api.patch(`/procurement/returns/${id}/kirim-pengganti`, data);
export const confirmReturn = (id, data) => api.patch(`/procurement/returns/${id}/confirm`, data);

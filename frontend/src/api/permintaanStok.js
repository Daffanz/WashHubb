import api from './axios';

export const getPermintaans = (params) => api.get('/operasional/permintaan-stok', { params });
export const getPermintaan = (id) => api.get(`/operasional/permintaan-stok/${id}`);
export const createPermintaan = (data) => api.post('/operasional/permintaan-stok', data);
export const deletePermintaan = (id) => api.delete(`/operasional/permintaan-stok/${id}`);
export const validatePermintaan = (id, data) => api.patch(`/operasional/permintaan-stok/${id}/validate`, data);

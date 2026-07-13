import api from './axios';

export const getPenerimaans = (params) => api.get('/operasional/penerimaan-stok-outlet', { params });
export const getPenerimaan = (id) => api.get(`/operasional/penerimaan-stok-outlet/${id}`);
export const createPenerimaan = (data) => api.post('/operasional/penerimaan-stok-outlet', data);

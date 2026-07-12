import api from './axios';
export const getReceipts = (params) => api.get('/procurement/receipts', { params });
export const getReceipt = (id) => api.get(`/procurement/receipts/${id}`);
export const createReceipt = (data) => api.post('/procurement/receipts', data);

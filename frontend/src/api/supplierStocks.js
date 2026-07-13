import api from './axios';

export const getSupplierStocks = (params) => api.get('/procurement/supplier-stocks', { params });
export const getSupplierStock = (id) => api.get(`/procurement/supplier-stocks/${id}`);
export const addSupplierStock = (data) => api.post('/procurement/supplier-stocks', data);
export const updateSupplierStock = (id, data) => api.put(`/procurement/supplier-stocks/${id}`, data);
export const deleteSupplierStock = (id) => api.delete(`/procurement/supplier-stocks/${id}`);

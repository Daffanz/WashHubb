import api from './axios';
export const getPurchaseOrders = (params) => api.get('/procurement/purchase-orders', { params });
export const getPurchaseOrder = (id) => api.get(`/procurement/purchase-orders/${id}`);
export const createPurchaseOrder = (data) => api.post('/procurement/purchase-orders', data);
export const updatePurchaseOrder = (id, data) => api.put(`/procurement/purchase-orders/${id}`, data);
export const deletePurchaseOrder = (id) => api.delete(`/procurement/purchase-orders/${id}`);
export const validatePurchaseOrder = (id, data) => api.patch(`/procurement/purchase-orders/${id}/validate`, data);

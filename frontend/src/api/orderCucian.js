import api from './axios';

export const getOrders = (params) => api.get('/operasional/orders', { params });
export const getOrder = (id) => api.get(`/operasional/orders/${id}`);
export const createOrder = (data) => api.post('/operasional/orders', data);
export const updateOrder = (id, data) => api.patch(`/operasional/orders/${id}`, data);

import api from './axios';
export const getStocks = (params) => api.get('/inventory/stocks', { params });
export const getStock = (type, id) => api.get(`/inventory/stocks/${type}/${id}`);

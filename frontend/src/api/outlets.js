import api from './axios';

export const getOutlets = (params) => api.get('/franchise/outlets', { params });
export const getOutlet = (id) => api.get(`/franchise/outlets/${id}`);
export const createOutlet = (data) => api.post('/franchise/outlets', data);
export const updateOutlet = (id, data) => api.put(`/franchise/outlets/${id}`, data);

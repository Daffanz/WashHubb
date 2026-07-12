import api from './axios';
export const getMutations = (params) => api.get('/inventory/mutations', { params });
export const createMutation = (data) => api.post('/inventory/mutations', data);

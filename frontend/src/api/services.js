import api from './axios';
export const getServices = (params) => api.get('/master/services', { params });
export const getService = (id) => api.get(`/master/services/${id}`);
export const createService = (data) => api.post('/master/services', data);
export const updateService = (id, data) => api.put(`/master/services/${id}`, data);
export const deleteService = (id) => api.delete(`/master/services/${id}`);
export const attachMaterial = (id, data) => api.post(`/master/services/${id}/materials`, data);
export const detachMaterial = (id, bahanBakuId) => api.delete(`/master/services/${id}/materials/${bahanBakuId}`);

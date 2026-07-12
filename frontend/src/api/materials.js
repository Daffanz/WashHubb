import api from './axios';
export const getMaterials = (params) => api.get('/master/materials', { params });
export const getMaterial = (id) => api.get(`/master/materials/${id}`);
export const createMaterial = (data) => api.post('/master/materials', data);
export const updateMaterial = (id, data) => api.put(`/master/materials/${id}`, data);
export const deleteMaterial = (id) => api.delete(`/master/materials/${id}`);

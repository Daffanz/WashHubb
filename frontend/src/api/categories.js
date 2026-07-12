import api from './axios';
export const getCategories = (params) => api.get('/master/categories', { params });
export const getCategory = (id) => api.get(`/master/categories/${id}`);
export const createCategory = (data) => api.post('/master/categories', data);
export const updateCategory = (id, data) => api.put(`/master/categories/${id}`, data);
export const deleteCategory = (id) => api.delete(`/master/categories/${id}`);

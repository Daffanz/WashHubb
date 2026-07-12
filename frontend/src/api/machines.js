import api from './axios';
export const getMachines = (params) => api.get('/master/machines', { params });
export const getMachine = (id) => api.get(`/master/machines/${id}`);
export const createMachine = (data) => api.post('/master/machines', data);
export const updateMachine = (id, data) => api.put(`/master/machines/${id}`, data);
export const deleteMachine = (id) => api.delete(`/master/machines/${id}`);

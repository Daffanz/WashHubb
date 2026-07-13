import api from './axios';

export const getJadwalServices = (params) => api.get('/operasional/jadwal-service', { params });
export const getJadwalService = (id) => api.get(`/operasional/jadwal-service/${id}`);
export const createJadwalService = (data) => api.post('/operasional/jadwal-service', data);
export const validateJadwalService = (id, data) => api.patch(`/operasional/jadwal-service/${id}/validate`, data);
export const completeJadwalService = (id) => api.patch(`/operasional/jadwal-service/${id}/complete`);

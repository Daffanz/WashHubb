import api from './axios';

export const getJadwalShifts = (params) => api.get('/operasional/jadwal-shift', { params });
export const getJadwalShift = (id) => api.get(`/operasional/jadwal-shift/${id}`);
export const createJadwalShift = (data) => api.post('/operasional/jadwal-shift', data);
export const updateJadwalShift = (id, data) => api.put(`/operasional/jadwal-shift/${id}`, data);
export const getJadwalShiftHistory = (params) => api.get('/operasional/jadwal-shift-history', { params });

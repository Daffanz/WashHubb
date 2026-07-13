import api from './axios';

export const getLoyaltis = (params) => api.get('/franchise/loyalti', { params });
export const getLoyalti = (id) => api.get(`/franchise/loyalti/${id}`);
export const createLoyalti = (data) => api.post('/franchise/loyalti', data);
export const evaluateLoyalti = (id, data) => api.patch(`/franchise/loyalti/${id}/evaluate`, data);
export const setBonusLoyalti = (id, data) => api.patch(`/franchise/loyalti/${id}/set-bonus`, data);
export const cairkanLoyalti = (id, data) => api.patch(`/franchise/loyalti/${id}/cairkan`, data);
export const confirmPencairan = (id, data) => api.patch(`/franchise/loyalti/${id}/confirm-pencairan`, data);

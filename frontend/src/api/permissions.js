import api from './axios';
export const getPermissions = (params) => api.get('/permissions', { params });

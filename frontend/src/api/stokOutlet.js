import api from './axios';

export const getStokOutlet = (params) => api.get('/inventory/stok-outlet', { params });

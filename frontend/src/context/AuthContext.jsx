import React, { createContext, useState, useEffect } from 'react';
import { getMe, login as apiLogin, logout as apiLogout } from '../api/auth';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('washhub_token');
    if (token) {
      getMe()
        .then((res) => setUser(res.data.data))
        .catch(() => { localStorage.removeItem('washhub_token'); localStorage.removeItem('washhub_user'); setUser(null); })
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, []);

  const login = async (email, password) => {
    const res = await apiLogin({ email, password });
    const { token } = res.data.data;
    localStorage.setItem('washhub_token', token);
    const meRes = await getMe();
    const userData = meRes.data.data;
    localStorage.setItem('washhub_user', JSON.stringify(userData));
    setUser(userData);
    return userData;
  };

  const logout = async () => {
    try { await apiLogout(); } catch (_) {}
    localStorage.removeItem('washhub_token');
    localStorage.removeItem('washhub_user');
    setUser(null);
  };

  const hasPermission = (kode) => user?.permissions?.includes(kode);
  const hasRole = (kode) => user?.role?.kode === kode;

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, hasPermission, hasRole }}>
      {children}
    </AuthContext.Provider>
  );
}

export { AuthContext };

import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth.js';
import toast from 'react-hot-toast';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await login(email, password);
      toast.success('Login berhasil!');
      navigate('/');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Login gagal');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex">
      <div className="hidden lg:flex lg:w-1/2 bg-wash-900 relative overflow-hidden items-center justify-center">
        <div className="absolute inset-0 bg-gradient-to-br from-wash-900 via-wash-800 to-wash-950"></div>
        <div className="relative z-10 text-center px-12">
          <div className="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-8 shadow-lg">
            <svg className="w-12 h-12 text-wash-900" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C8.13 2 5 5.13 5 9c0 2.38 1.19 4.47 3 5.74V17a1 1 0 001 1h6a1 1 0 001-1v-2.26c1.81-1.27 3-3.36 3-5.74 0-3.87-3.13-7-7-7zm2 14h-4v-1h4v1zm0-2h-4v-1h4v1zm1.15-4.95L14 11.12V15h-4v-3.88l-1.15-1.07A4.993 4.993 0 017 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.63-.8 3.16-2.15 4.05z" />
            </svg>
          </div>
          <h1 className="text-4xl font-bold text-white mb-4">WashHub</h1>
          <p className="text-wash-200 text-lg">Sistem Manajemen Franchise Laundry</p>
        </div>
      </div>
      <div className="flex-1 flex items-center justify-center px-6 py-12 bg-white">
        <div className="w-full max-w-md">
          <div className="lg:hidden flex items-center gap-3 mb-8">
            <div className="w-10 h-10 bg-wash-900 rounded-xl flex items-center justify-center">
              <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 2.38 1.19 4.47 3 5.74V17a1 1 0 001 1h6a1 1 0 001-1v-2.26c1.81-1.27 3-3.36 3-5.74 0-3.87-3.13-7-7-7zm2 14h-4v-1h4v1zm0-2h-4v-1h4v1zm1.15-4.95L14 11.12V15h-4v-3.88l-1.15-1.07A4.993 4.993 0 017 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.63-.8 3.16-2.15 4.05z" />
              </svg>
            </div>
            <span className="text-xl font-bold text-wash-900">WashHub</span>
          </div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Masuk ke Akun</h2>
          <p className="text-gray-500 mb-8">Masukkan email dan password Anda untuk melanjutkan</p>
          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
              <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required className="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-wash-500 focus:border-wash-500 transition" placeholder="Masukkan email" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
              <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required className="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-wash-500 focus:border-wash-500 transition" placeholder="Masukkan password" />
            </div>
            <button type="submit" disabled={loading} className="w-full py-3 bg-wash-900 text-white font-semibold rounded-xl hover:bg-wash-800 transition shadow-sm disabled:opacity-50 text-sm">
              {loading ? 'Masuk...' : 'Masuk'}
            </button>
            <div className="text-center">
              <Link to="/forgot-password" className="text-sm text-wash-700 hover:text-wash-900 font-medium">Lupa Password?</Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}

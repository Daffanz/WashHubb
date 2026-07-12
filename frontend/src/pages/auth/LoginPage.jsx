import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth.js';
import toast from 'react-hot-toast';
import logoSrc from '../../assets/logo_washhub.png';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const { login } = useAuth();
  const navigate = useNavigate();

  const clearError = () => setErrorMsg('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    clearError();

    // Validasi form kosong
    if (!email.trim()) {
      setErrorMsg('Email harus diisi.');
      toast.error('Email harus diisi.');
      return;
    }
    if (!password.trim()) {
      setErrorMsg('Password harus diisi.');
      toast.error('Password harus diisi.');
      return;
    }

    setLoading(true);
    try {
      await login(email, password);
      toast.success('Login berhasil!');
      navigate('/');
    } catch (err) {
      const status = err.response?.status;
      const message = err.response?.data?.message || 'Login gagal';

      if (status === 422) {
        // Validation error dari backend (format email tidak valid dll)
        const errors = err.response?.data?.errors;
        if (errors?.email) {
          const msg = errors.email[0];
          setErrorMsg(msg);
          toast.error(msg);
        } else if (errors?.password) {
          const msg = errors.password[0];
          setErrorMsg(msg);
          toast.error(msg);
        } else {
          setErrorMsg(message);
          toast.error(message);
        }
      } else if (message === 'Email tidak terdaftar.' || message === 'Password salah.') {
        setErrorMsg('Email atau password salah.');
        toast.error('Email atau password salah.');
      } else {
        setErrorMsg(message);
        toast.error(message);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex">
      <div className="hidden lg:flex lg:w-1/2 bg-wash-900 relative overflow-hidden items-center justify-center">
        <div className="absolute inset-0 bg-gradient-to-br from-wash-900 via-wash-800 to-wash-950"></div>

        {/* Gelembung dekoratif */}
        <div className="absolute top-[10%] left-[15%] w-32 h-32 bg-white/10 rounded-full blur-sm"></div>
        <div className="absolute top-[30%] right-[10%] w-20 h-20 bg-white/5 rounded-full blur-sm"></div>
        <div className="absolute bottom-[20%] left-[20%] w-40 h-40 bg-white/5 rounded-full blur-sm"></div>
        <div className="absolute top-[5%] right-[25%] w-16 h-16 bg-white/10 rounded-full blur-sm"></div>
        <div className="absolute bottom-[35%] right-[15%] w-24 h-24 bg-white/5 rounded-full blur-sm"></div>
        <div className="absolute bottom-[10%] right-[35%] w-14 h-14 bg-white/10 rounded-full blur-sm"></div>
        <div className="absolute top-[55%] left-[5%] w-12 h-12 bg-white/5 rounded-full blur-sm"></div>
        <div className="absolute top-[15%] left-[45%] w-8 h-8 bg-white/10 rounded-full blur-sm"></div>
        <div className="absolute bottom-[5%] left-[45%] w-28 h-28 bg-white/5 rounded-full blur-sm"></div>

        <div className="relative z-10 text-center px-12">
          <img src={logoSrc} alt="WashHub" className="w-[500px] h-[500px] mx-auto object-contain mb-0" />
          <p className="text-wash-200 text-lg -mt-8">Sistem Manajemen Franchise Laundry</p>
        </div>
      </div>
      <div className="flex-1 flex items-center justify-center px-6 py-12 bg-white">
        <div className="w-full max-w-md">
          <div className="lg:hidden flex items-center gap-3 mb-8">
            <img src={logoSrc} alt="WashHub" className="w-14 h-14 object-contain" />
          </div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Masuk ke Akun</h2>
          <p className="text-gray-500 mb-8">Masukkan email dan password Anda untuk melanjutkan</p>

          {/* Inline error notification */}
          {errorMsg && (
            <div className="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
              <svg className="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div className="flex-1">
                <p className="text-sm font-medium text-red-800">{errorMsg}</p>
              </div>
              <button type="button" onClick={clearError} className="text-red-400 hover:text-red-600 transition">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-wash-500 focus:border-wash-500 transition"
                placeholder="Masukkan email"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
              <div className="relative">
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full px-4 py-3 pr-12 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-wash-500 focus:border-wash-500 transition"
                  placeholder="Masukkan password"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition p-1"
                  tabIndex={-1}
                >
                  {showPassword ? (
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                  ) : (
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  )}
                </button>
              </div>
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

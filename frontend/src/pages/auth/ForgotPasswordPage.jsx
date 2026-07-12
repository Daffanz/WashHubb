import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import toast from 'react-hot-toast';

export default function ForgotPasswordPage() {
  const navigate = useNavigate();
  const [step, setStep] = useState(1); // 1 = check email, 2 = reset password
  const [email, setEmail] = useState('');
  const [userName, setUserName] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const handleCheckEmail = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const res = await fetch('/api/auth/forgot-password/check-email', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ email }),
      });
      const data = await res.json();
      if (!res.ok) {
        setErrors({ email: data.message });
        toast.error(data.message);
        setLoading(false);
        return;
      }
      setUserName(data.data.nama);
      setStep(2);
      toast.success('Email ditemukan!');
    } catch {
      toast.error('Terjadi kesalahan');
    }
    setLoading(false);
  };

  const handleResetPassword = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});

    if (password !== passwordConfirmation) {
      setErrors({ password_confirmation: 'Konfirmasi password tidak cocok.' });
      setLoading(false);
      return;
    }

    try {
      const res = await fetch('/api/auth/forgot-password/reset', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ email, password, password_confirmation: passwordConfirmation }),
      });
      const data = await res.json();
      if (!res.ok) {
        setErrors(data.errors || { general: data.message });
        toast.error(data.message || 'Gagal reset password');
        setLoading(false);
        return;
      }
      toast.success('Password berhasil direset! Silakan login.');
      navigate('/login');
    } catch {
      toast.error('Terjadi kesalahan');
    }
    setLoading(false);
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
          <p className="text-wash-200 text-lg">Reset Password</p>
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

          {step === 1 ? (
            <>
              <h2 className="text-2xl font-bold text-gray-900 mb-2">Lupa Password?</h2>
              <p className="text-gray-500 mb-8">Masukkan email Anda yang terdaftar di sistem</p>
              <form onSubmit={handleCheckEmail} className="space-y-5">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                  <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required className="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-wash-500 focus:border-wash-500 transition" placeholder="Masukkan email" />
                  {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                </div>
                <button type="submit" disabled={loading} className="w-full py-3 bg-wash-900 text-white font-semibold rounded-xl hover:bg-wash-800 transition shadow-sm disabled:opacity-50 text-sm">
                  {loading ? 'Mengecek...' : 'Cek Email'}
                </button>
              </form>
            </>
          ) : (
            <>
              <div className="flex items-center gap-3 mb-6 p-4 bg-wash-50 rounded-xl border border-wash-200">
                <div className="w-10 h-10 rounded-full bg-wash-100 flex items-center justify-center flex-shrink-0">
                  <svg className="w-5 h-5 text-wash-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
                </div>
                <div>
                  <p className="text-sm font-medium text-wash-900">Email ditemukan!</p>
                  <p className="text-xs text-wash-600">Halo, <strong>{userName}</strong>. Masukkan password baru Anda.</p>
                </div>
              </div>
              <h2 className="text-2xl font-bold text-gray-900 mb-2">Reset Password</h2>
              <p className="text-gray-500 mb-8">Masukkan password baru dan konfirmasi</p>
              <form onSubmit={handleResetPassword} className="space-y-5">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                  <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required minLength={8} className="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-wash-500 focus:border-wash-500 transition" placeholder="Minimal 8 karakter" />
                  {errors.password && <p className="mt-1 text-xs text-red-600">{errors.password[0]}</p>}
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password</label>
                  <input type="password" value={passwordConfirmation} onChange={(e) => setPasswordConfirmation(e.target.value)} required minLength={8} className="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-wash-500 focus:border-wash-500 transition" placeholder="Ulangi password baru" />
                  {errors.password_confirmation && <p className="mt-1 text-xs text-red-600">{errors.password_confirmation}</p>}
                </div>
                <button type="submit" disabled={loading} className="w-full py-3 bg-wash-900 text-white font-semibold rounded-xl hover:bg-wash-800 transition shadow-sm disabled:opacity-50 text-sm">
                  {loading ? 'Meriset...' : 'Reset Password'}
                </button>
              </form>
            </>
          )}

          <p className="mt-6 text-center text-sm text-gray-500">
            <Link to="/login" className="text-wash-700 hover:text-wash-900 font-medium">← Kembali ke Login</Link>
          </p>
        </div>
      </div>
    </div>
  );
}

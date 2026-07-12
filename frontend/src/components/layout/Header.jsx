import React from 'react';
import { useAuth } from '../../hooks/useAuth.js';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';

export default function Header({ onMenuToggle }) {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    toast.success('Berhasil logout');
    navigate('/login');
  };

  return (
    <header className="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
      <button onClick={onMenuToggle} className="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition">
        <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      <div className="hidden lg:block">
        <p className="text-sm text-gray-500">Selamat datang, <span className="font-semibold text-gray-900">{user?.nama || 'User'}</span></p>
      </div>
      <div className="flex items-center gap-4">
        <button onClick={handleLogout} className="flex items-center gap-2 text-sm text-gray-500 hover:text-red-600 transition px-3 py-2 rounded-lg hover:bg-red-50">
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
          </svg>
          Logout
        </button>
      </div>
    </header>
  );
}

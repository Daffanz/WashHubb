import React from 'react';
import { useNavigate } from 'react-router-dom';

export default function FormActions({ loading, onCancel }) {
  const navigate = useNavigate();
  const handleCancel = onCancel || (() => navigate(-1));
  return (
    <div className="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
      <button type="button" onClick={handleCancel} className="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
        Batal
      </button>
      <button type="submit" disabled={loading} className="px-5 py-2.5 text-sm font-medium text-white bg-wash-900 rounded-lg hover:bg-wash-800 transition shadow-sm disabled:opacity-50">
        {loading ? 'Menyimpan...' : 'Simpan'}
      </button>
    </div>
  );
}

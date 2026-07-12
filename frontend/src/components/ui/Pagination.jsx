import React from 'react';

export default function Pagination({ meta, onPageChange }) {
  if (!meta || meta.last_page <= 1) return null;
  const pages = [];
  for (let i = 1; i <= meta.last_page; i++) pages.push(i);

  return (
    <div className="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
      <div className="text-sm text-gray-500">
        Menampilkan {((meta.current_page - 1) * meta.per_page) + 1} - {Math.min(meta.current_page * meta.per_page, meta.total)} dari {meta.total}
      </div>
      <div className="flex gap-1">
        <button onClick={() => onPageChange(meta.current_page - 1)} disabled={meta.current_page <= 1} className="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
          Prev
        </button>
        {pages.map((p) => (
          <button key={p} onClick={() => onPageChange(p)} className={`px-3 py-1.5 text-sm rounded-lg border transition ${p === meta.current_page ? 'bg-wash-900 text-white border-wash-900' : 'border-gray-200 hover:bg-gray-50'}`}>
            {p}
          </button>
        ))}
        <button onClick={() => onPageChange(meta.current_page + 1)} disabled={meta.current_page >= meta.last_page} className="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
          Next
        </button>
      </div>
    </div>
  );
}

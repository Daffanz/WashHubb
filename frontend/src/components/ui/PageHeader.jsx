import React from 'react';
import { Link } from 'react-router-dom';

export default function PageHeader({ title, breadcrumbs = [], actionLabel, actionTo }) {
  return (
    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">{title}</h1>
        {breadcrumbs.length > 0 && (
          <nav className="mt-1 flex text-sm text-gray-500">
            {breadcrumbs.map((b, i) => (
              <span key={i} className="flex items-center">
                {i > 0 && <span className="mx-2 text-gray-300">/</span>}
                {b.to ? (
                  <Link to={b.to} className="hover:text-wash-700 transition">{b.label}</Link>
                ) : (
                  <span className="text-gray-700 font-medium">{b.label}</span>
                )}
              </span>
            ))}
          </nav>
        )}
      </div>
      {actionLabel && actionTo && (
        <Link
          to={actionTo}
          className="inline-flex items-center gap-2 bg-wash-900 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-wash-800 transition shadow-sm"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
          </svg>
          {actionLabel}
        </Link>
      )}
    </div>
  );
}

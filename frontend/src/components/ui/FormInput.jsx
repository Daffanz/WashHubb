import React from 'react';

export default function FormInput({ label, name, type = 'text', value, onChange, error, required, placeholder, disabled }) {
  return (
    <div>
      <label htmlFor={name} className="block text-sm font-medium text-gray-700 mb-1.5">
        {label} {required && <span className="text-red-500">*</span>}
      </label>
      <input
        id={name}
        name={name}
        type={type}
        value={value}
        onChange={onChange}
        placeholder={placeholder}
        disabled={disabled}
        className={`w-full px-3 py-2.5 text-sm border rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-wash-500 focus:border-wash-500 disabled:bg-gray-50 disabled:text-gray-500 ${error ? 'border-red-300' : 'border-gray-300'}`}
      />
      {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
    </div>
  );
}

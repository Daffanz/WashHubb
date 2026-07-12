import React from 'react';
export default function FormGrid({ children, className = '' }) {
  return <div className={`grid grid-cols-1 md:grid-cols-2 gap-5 ${className}`}>{children}</div>;
}

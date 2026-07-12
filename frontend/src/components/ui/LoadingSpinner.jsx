import React from 'react';
export default function LoadingSpinner({ fullScreen }) {
  if (fullScreen) return (
    <div className="flex items-center justify-center h-64">
      <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-wash-900"></div>
    </div>
  );
  return <div className="flex justify-center py-8"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>;
}

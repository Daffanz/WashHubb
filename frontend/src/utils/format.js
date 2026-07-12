export const formatRupiah = (num) => {
  if (num === null || num === undefined) return '-';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
};

export const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

export const formatQty = (num) => {
  if (num === null || num === undefined) return '-';
  const n = parseFloat(num);
  return Number.isInteger(n) ? n.toString() : n.toFixed(2).replace(/\.?0+$/, '');
};

export const formatDateTime = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

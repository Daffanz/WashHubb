export const ROLE_OPTIONS = [
  { value: 'admin_it', label: 'Admin IT' },
  { value: 'franchisor', label: 'Franchisor' },
  { value: 'procurement', label: 'Procurement' },
  { value: 'supplier', label: 'Supplier' },
  { value: 'manager_outlet', label: 'Manager Outlet' },
];

export const JENIS_SUPPLIER_OPTIONS = [
  { value: 'bahan_baku', label: 'Bahan Baku' },
  { value: 'mesin', label: 'Mesin' },
];

export const JENIS_PO_OPTIONS = [
  { value: 'bahan_baku', label: 'Bahan Baku' },
  { value: 'mesin', label: 'Mesin' },
];

export const STATUS_VALIDASI_OPTIONS = [
  { value: 'disetujui', label: 'Disetujui' },
  { value: 'ditolak', label: 'Ditolak' },
];

export const JENIS_MUTASI_OPTIONS = [
  { value: 'masuk', label: 'Masuk' },
  { value: 'keluar', label: 'Keluar' },
];

export const STATUS_COLOR_MAP = {
  aktif: 'bg-emerald-100 text-emerald-800',
  nonaktif: 'bg-red-100 text-red-800',
  diajukan: 'bg-yellow-100 text-yellow-800',
  dikirim: 'bg-blue-100 text-blue-800',
  dikirim_sebagian: 'bg-blue-100 text-blue-800',
  disetujui: 'bg-emerald-100 text-emerald-800',
  disetujui_sebagian: 'bg-amber-100 text-amber-800',
  ditolak: 'bg-red-100 text-red-800',
  diterima: 'bg-emerald-100 text-emerald-800',
  selesai: 'bg-emerald-100 text-emerald-800',
  menunggu_pengganti: 'bg-yellow-100 text-yellow-800',
  pengganti_dikirim: 'bg-blue-100 text-blue-800',
};

import React, { useState, useEffect, useCallback } from 'react';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import { formatQty } from '../../utils/format';

export default function StockList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [type, setType] = useState('bahan');

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await fetch(`/api/inventory/stocks?type=${type}&page=${p}`, { headers: { Authorization: `Bearer ${localStorage.getItem('washhub_token')}` } }); const d = await res.json(); setData(d.data); setMeta(d.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page, type]);

  useEffect(() => { setPage(1); fetchData(1); }, [type]);

  const columns = type === 'bahan' ? [
    { label: 'Nama Bahan', render: (r) => r.bahan_baku?.nama || '-' },
    { label: 'Kategori', render: (r) => r.bahan_baku?.kategori || '-' },
    { label: 'Stok Masuk', render: (r) => <span className="text-emerald-600 font-medium">{formatQty(r.stok_masuk)}</span> },
    { label: 'Stok Keluar', render: (r) => <span className="text-red-600 font-medium">{formatQty(r.stok_keluar)}</span> },
    { label: 'Stok Saat Ini', render: (r) => <span className="font-bold text-wash-800">{formatQty(r.stok_saat_ini)}</span> },
  ] : [
    { label: 'Nama Mesin', render: (r) => r.mesin?.nama || '-' },
    { label: 'Kode', render: (r) => r.mesin?.kode_mesin || '-' },
    { label: 'Stok Masuk', render: (r) => <span className="text-emerald-600 font-medium">{formatQty(r.stok_masuk)}</span> },
    { label: 'Stok Keluar', render: (r) => <span className="text-red-600 font-medium">{formatQty(r.stok_keluar)}</span> },
    { label: 'Stok Saat Ini', render: (r) => <span className="font-bold text-wash-800">{formatQty(r.stok_saat_ini)}</span> },
  ];

  return (
    <div>
      <PageHeader title="Stok Perusahaan (Gudang Pusat)" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Inventory' }, { label: 'Stok Perusahaan' }]} />
      <div className="mb-4 flex gap-2">
        <button onClick={() => setType('bahan')} className={`px-4 py-2 text-sm font-medium rounded-lg transition ${type === 'bahan' ? 'bg-wash-900 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'}`}>Bahan Baku</button>
        <button onClick={() => setType('mesin')} className={`px-4 py-2 text-sm font-medium rounded-lg transition ${type === 'mesin' ? 'bg-wash-900 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'}`}>Mesin</button>
      </div>
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)} />
    </div>
  );
}

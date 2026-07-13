import React, { useState, useEffect, useCallback } from 'react';
import toast from 'react-hot-toast';
import { getStokOutlet } from '../../api/stokOutlet';
import { getOutlets } from '../../api/outlets';
import { useAuth } from '../../hooks/useAuth';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import FormSelect from '../../components/ui/FormSelect';

export default function StokOutletList() {
  const { user } = useAuth();
  const roleKode = user?.role?.kode;
  const canFilterOutlet = ['admin_it', 'franchisor', 'procurement'].includes(roleKode);

  const [type, setType] = useState('bahan_baku');
  const [outletId, setOutletId] = useState('');
  const [outlets, setOutlets] = useState([]);
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  useEffect(() => {
    if (canFilterOutlet) {
      getOutlets({ per_page: 100 }).then((res) => setOutlets([{ value: '', label: 'Semua Outlet' }, ...res.data.data.map((o) => ({ value: o.id, label: o.nama }))])).catch(() => {});
    }
  }, [canFilterOutlet]);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try {
      const params = { page: p, type };
      if (canFilterOutlet && outletId) params.outlet_id = outletId;
      const res = await getStokOutlet(params);
      setData(res.data.data);
      setMeta(res.data.meta);
    } catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page, type, outletId, canFilterOutlet]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const bahanBakuColumns = [
    ...(canFilterOutlet ? [{ label: 'Outlet', render: (r) => r.outlet?.nama || '-' }] : []),
    { label: 'Bahan Baku', render: (r) => r.bahan_baku?.nama || '-' },
    { label: 'Kategori', render: (r) => r.bahan_baku?.kategori || '-' },
    { label: 'Satuan', render: (r) => r.bahan_baku?.satuan || '-' },
    { label: 'Stok Saat Ini', render: (r) => <span className="font-semibold">{r.stok_saat_ini}</span> },
    { label: 'Stok Masuk', render: (r) => r.stok_masuk },
    { label: 'Stok Keluar', render: (r) => r.stok_keluar },
  ];

  const mesinColumns = [
    ...(canFilterOutlet ? [{ label: 'Outlet', render: (r) => r.outlet?.nama || '-' }] : []),
    { label: 'Mesin', render: (r) => r.mesin?.nama || '-' },
    { label: 'Stok Saat Ini', render: (r) => <span className="font-semibold">{r.stok_saat_ini}</span> },
    { label: 'Stok Masuk', render: (r) => r.stok_masuk },
    { label: 'Stok Keluar', render: (r) => r.stok_keluar },
  ];

  return (
    <div>
      <PageHeader title="Stok Outlet" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Stok Outlet' }]} />
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
        <div className={`grid grid-cols-1 ${canFilterOutlet ? 'md:grid-cols-2' : ''} gap-4`}>
          <FormSelect label="Tipe Stok" value={type} onChange={(e) => { setType(e.target.value); setPage(1); }} options={[{ value: 'bahan_baku', label: 'Bahan Baku' }, { value: 'mesin', label: 'Mesin' }]} />
          {canFilterOutlet && (
            <FormSelect label="Outlet" value={outletId} onChange={(e) => { setOutletId(e.target.value); setPage(1); }} options={outlets} placeholder="Semua Outlet" />
          )}
        </div>
      </div>
      <DataTable columns={type === 'mesin' ? mesinColumns : bahanBakuColumns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)} />
    </div>
  );
}

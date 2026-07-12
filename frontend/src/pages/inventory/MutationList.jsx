import React, { useState, useEffect, useCallback } from 'react';
import toast from 'react-hot-toast';
import { getMutations } from '../../api/mutations';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import { formatDateTime } from '../../utils/format';

export default function MutationList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getMutations({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'Item', render: (r) => <span className="font-medium">{r.stok_type?.replace('App\\Models\\StokPusat', '') || r.stok_type}</span> },
    { label: 'Jenis Mutasi', render: (r) => {
      const colors = { masuk: 'text-emerald-600', keluar: 'text-red-600', penyesuaian: 'text-yellow-600' };
      return <span className={`font-medium capitalize ${colors[r.jenis_mutasi] || ''}`}>{r.jenis_mutasi}</span>;
    }},
    { label: 'Jumlah', render: (r) => <span className="font-semibold">{r.jumlah}</span> },
    { label: 'Tanggal', render: (r) => formatDateTime(r.tanggal) },
    { label: 'Keterangan', render: (r) => <span className="max-w-[200px] truncate inline-block text-gray-500">{r.keterangan || '-'}</span> },
  ];

  return (
    <div>
      <PageHeader title="Mutasi Stok" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Inventory' }, { label: 'Mutasi' }]} actionLabel="Buat Mutasi" actionTo="/inventory/mutations/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)} />
    </div>
  );
}

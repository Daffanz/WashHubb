import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getPenerimaans } from '../../../api/penerimaanStokOutlet';
import PageHeader from '../../../components/ui/PageHeader';
import DataTable from '../../../components/ui/DataTable';
import { formatDate } from '../../../utils/format';

export default function PenerimaanList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getPenerimaans({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Outlet', render: (row) => row.distribusi?.outlet || '-' },
    { label: 'Distribusi ID', render: (row) => row.distribusi?.id ? `#${row.distribusi.id}` : '-' },
    { label: 'Tanggal Terima', render: (row) => formatDate(row.tanggal_terima) },
    { label: 'Diterima Oleh', key: 'user' },
  ];

  return (
    <div>
      <PageHeader title="Penerimaan Stok Outlet" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Penerimaan Stok Outlet' }]} actionLabel="Buat Penerimaan" actionTo="/operasional/penerimaan-stok-outlet/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <Link to={`/operasional/penerimaan-stok-outlet/${row.id}`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Detail</Link>
        )}
      />
    </div>
  );
}

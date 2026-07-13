import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getJadwalServices } from '../../../api/jadwalService';
import PageHeader from '../../../components/ui/PageHeader';
import DataTable from '../../../components/ui/DataTable';
import StatusBadge from '../../../components/ui/StatusBadge';
import { formatDate } from '../../../utils/format';

export default function ServiceList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getJadwalServices({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'Mesin', render: (r) => r.detail_mesin?.mesin || '-' },
    { label: 'No. Seri', render: (r) => r.detail_mesin?.nomor_seri || '-' },
    { label: 'Outlet', render: (r) => r.outlet?.nama || '-' },
    { label: 'Tanggal Pengajuan', render: (r) => formatDate(r.tanggal_pengajuan) },
    { label: 'Menunggu Mesin', render: (r) => r.menunggu_mesin_bebas ? <span className="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Ya</span> : '-' },
    { label: 'Status', render: (r) => {
      const colors = { menunggu_persetujuan: 'yellow', disetujui: 'green', ditolak: 'red', selesai: 'green' };
      return <StatusBadge status={r.status?.label} color={colors[r.status?.kode] || 'gray'} />;
    }},
  ];

  return (
    <div>
      <PageHeader title="Jadwal Service Mesin" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Operasional' }, { label: 'Service' }]} actionLabel="Ajukan Service" actionTo="/operasional/jadwal-service/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <Link to={`/operasional/jadwal-service/${row.id}`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Detail</Link>
        )}
      />
    </div>
  );
}

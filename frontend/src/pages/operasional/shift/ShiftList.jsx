import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getJadwalShifts } from '../../../api/jadwalShift';
import PageHeader from '../../../components/ui/PageHeader';
import DataTable from '../../../components/ui/DataTable';
import StatusBadge from '../../../components/ui/StatusBadge';
import { formatDate } from '../../../utils/format';

export default function ShiftList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getJadwalShifts({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'ID', render: (r) => <span className="font-mono text-sm">#{r.id}</span> },
    { label: 'Outlet', render: (r) => r.outlet?.nama || '-' },
    { label: 'Minggu Mulai', render: (r) => formatDate(r.minggu_mulai) },
    { label: 'Staf', render: (r) => <span className="text-sm text-gray-500">{r.details?.length || 0} shift</span> },
    { label: 'Status', render: (r) => {
      const semuaSelesai = r.details?.every((d) => d.status?.kode === 'selesai');
      const adaYangBerjalan = r.details?.some((d) => d.status?.kode === 'berjalan');
      const status = semuaSelesai ? 'Selesai' : adaYangBerjalan ? 'Berjalan' : 'Belum Berjalan';
      const color = semuaSelesai ? 'green' : adaYangBerjalan ? 'blue' : 'gray';
      return <StatusBadge status={status} color={color} />;
    }},
  ];

  return (
    <div>
      <PageHeader title="Jadwal Shift Staf" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Operasional' }, { label: 'Shift' }]} actionLabel="Buat Jadwal" actionTo="/operasional/jadwal-shift/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => {
          const semuaBelumBerjalan = row.details?.every((d) => d.status?.kode === 'belum_berjalan');
          return (
            <div className="flex gap-2 justify-end">
              <Link to={`/operasional/jadwal-shift/${row.id}`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Detail</Link>
              {semuaBelumBerjalan && <Link to={`/operasional/jadwal-shift/${row.id}/edit`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Edit</Link>}
            </div>
          );
        }}
      />
    </div>
  );
}

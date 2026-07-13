import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getLoyaltis } from '../../../api/loyalti';
import PageHeader from '../../../components/ui/PageHeader';
import DataTable from '../../../components/ui/DataTable';
import StatusBadge from '../../../components/ui/StatusBadge';
import { formatRupiah } from '../../../utils/format';
import { STATUS_COLOR_MAP } from '../../../utils/constants';

export default function LoyaltiList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getLoyaltis({ page: p }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const columns = [
    { label: 'ID', render: (r) => <span className="font-mono text-sm">#{r.id}</span> },
    { label: 'Outlet', render: (r) => r.outlet?.nama || '-' },
    { label: 'Periode', key: 'periode' },
    { label: 'Target Omset', render: (r) => formatRupiah(r.target_omset) },
    { label: 'Omset Aktual', render: (r) => <span className={r.omset_aktual >= r.target_omset ? 'text-emerald-600 font-medium' : 'text-red-600'}>{formatRupiah(r.omset_aktual)}</span> },
    { label: 'Memenuhi', render: (r) => r.memenuhi_target ? <span className="text-emerald-600 text-xs font-medium">Ya</span> : <span className="text-red-600 text-xs font-medium">Tidak</span> },
    { label: 'Status', render: (r) => <StatusBadge status={r.status?.label} color={STATUS_COLOR_MAP[r.status?.kode]?.includes('emerald') ? 'green' : STATUS_COLOR_MAP[r.status?.kode]?.includes('red') ? 'red' : STATUS_COLOR_MAP[r.status?.kode]?.includes('yellow') ? 'yellow' : STATUS_COLOR_MAP[r.status?.kode]?.includes('purple') ? 'purple' : 'blue'} /> },
  ];

  return (
    <div>
      <PageHeader title="Loyalti (Bonus Kinerja)" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Franchise' }, { label: 'Loyalti' }]} actionLabel="Buat Target" actionTo="/franchise/loyalti/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <Link to={`/franchise/loyalti/${row.id}`} className="text-sm text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50 transition">Detail</Link>
        )}
      />
    </div>
  );
}

import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import StatusBadge from '../../components/ui/StatusBadge';
import { formatDateTime } from '../../utils/format';

export default function ReturnList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try {
      const res = await fetch(`/api/procurement/returns?page=${p}`, { headers: { Authorization: `Bearer ${localStorage.getItem('washhub_token')}` } });
      const d = await res.json();
      setData(d.data); setMeta(d.meta);
    } catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  if (loading) return <LoadingSpinner />;

  return (
    <div>
      <PageHeader title="Retur Barang" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Pengadaan' }, { label: 'Retur' }]} />
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
              <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal Retur</th>
              <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
              <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Detail Bahan</th>
              <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Detail Mesin</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {data.length === 0 ? (
              <tr><td colSpan={5} className="px-4 py-12 text-center text-sm text-gray-400">Belum ada retur</td></tr>
            ) : data.map((r) => (
              <tr key={r.id} className="hover:bg-gray-50/50">
                <td className="px-4 py-3 text-sm font-medium">#{r.id}</td>
                <td className="px-4 py-3 text-sm text-gray-600">{formatDateTime(r.tanggal_retur)}</td>
                <td className="px-4 py-3"><StatusBadge status={r.status?.label} color={r.status?.kode === 'selesai' ? 'green' : r.status?.kode === 'pengganti_dikirim' ? 'blue' : 'yellow'} /></td>
                <td className="px-4 py-3 text-sm text-gray-600">{r.detail_bahan_baku?.length || 0} item</td>
                <td className="px-4 py-3 text-sm text-gray-600">{r.detail_mesin?.length || 0} item</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

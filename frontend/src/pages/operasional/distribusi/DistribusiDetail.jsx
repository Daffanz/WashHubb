import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getDistribusi } from '../../../api/distribusiOutlet';
import PageHeader from '../../../components/ui/PageHeader';
import StatusBadge from '../../../components/ui/StatusBadge';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import { formatQty, formatDate } from '../../../utils/format';

export default function DistribusiDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [distribusi, setDistribusi] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchData = useCallback(async () => {
    try { const res = await getDistribusi(id); setDistribusi(res.data.data); }
    catch { toast.error('Gagal memuat data'); navigate(-1); }
    finally { setLoading(false); }
  }, [id, navigate]);

  useEffect(() => { fetchData(); }, [fetchData]);

  if (loading) return <LoadingSpinner />;
  if (!distribusi) return null;

  return (
    <div>
      <PageHeader title={`Distribusi #${distribusi.id}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Distribusi', to: '/operasional/distribusi-outlet' }, { label: 'Detail' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Distribusi</h3>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Permintaan</dt><dd>#{distribusi.permintaan?.id || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Outlet</dt><dd>{distribusi.permintaan?.outlet || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Tanggal Kirim</dt><dd>{formatDate(distribusi.tanggal_kirim)}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={distribusi.status?.label} color={distribusi.status?.kode === 'diterima' ? 'green' : 'blue'} /></dd></div>
          </dl>
        </div>

        <div className="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Items</h3>
          <div className="overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead><tr className="border-b">
                <th className="text-left py-2 text-gray-500">Tipe</th>
                <th className="text-left py-2 text-gray-500">Item</th>
                <th className="text-right py-2 text-gray-500">Jumlah Kirim</th>
              </tr></thead>
              <tbody>
                {distribusi.details?.map((d) => (
                  <tr key={d.id} className="border-b last:border-0">
                    <td className="py-2">
                      <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${d.tipe_item === 'mesin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}`}>
                        {d.tipe_item === 'mesin' ? 'Mesin' : 'Bahan Baku'}
                      </span>
                    </td>
                    <td className="py-2 font-medium">{d.tipe_item === 'mesin' ? d.mesin?.nama : d.bahan_baku?.nama || '-'}</td>
                    <td className="py-2 text-right">{formatQty(d.jumlah_kirim)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
}

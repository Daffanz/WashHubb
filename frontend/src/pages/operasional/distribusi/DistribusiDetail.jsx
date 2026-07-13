import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getDistribusi, terimaDistribusi } from '../../../api/distribusiOutlet';
import PageHeader from '../../../components/ui/PageHeader';
import StatusBadge from '../../../components/ui/StatusBadge';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import { formatQty, formatDate } from '../../../utils/format';

export default function DistribusiDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [distribusi, setDistribusi] = useState(null);
  const [loading, setLoading] = useState(true);
  const [qtyMap, setQtyMap] = useState({});
  const [processing, setProcessing] = useState(false);

  const fetchData = useCallback(async () => {
    try { const res = await getDistribusi(id); setDistribusi(res.data.data); }
    catch { toast.error('Gagal memuat data'); navigate(-1); }
    finally { setLoading(false); }
  }, [id, navigate]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const isDikirim = distribusi?.status?.kode === 'dikirim' || distribusi?.status?.kode === 'dikirim_sebagian';

  const handleTerima = async () => {
    const items = [];
    for (const d of distribusi.details) {
      const qty = qtyMap[d.id];
      if (!qty || parseFloat(qty) <= 0) { toast.error(`Qty diterima untuk "${d.bahan_baku?.nama}" wajib diisi`); return; }
      items.push({ distribusi_outlet_detail_id: d.id, qty_diterima: parseFloat(qty) });
    }

    setProcessing(true);
    try {
      await terimaDistribusi(id, { items });
      toast.success('Penerimaan berhasil dicatat');
      await fetchData();
      setQtyMap({});
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setProcessing(false);
  };

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
                <th className="text-left py-2 text-gray-500">Bahan Baku</th>
                <th className="text-right py-2 text-gray-500">Jumlah Kirim</th>
                {isDikirim && <th className="text-right py-2 text-gray-500">Qty Diterima</th>}
              </tr></thead>
              <tbody>
                {distribusi.details?.map((d) => (
                  <tr key={d.id} className="border-b last:border-0">
                    <td className="py-2 font-medium">{d.bahan_baku?.nama || '-'}</td>
                    <td className="py-2 text-right">{formatQty(d.jumlah_kirim)}</td>
                    {isDikirim && (
                      <td className="py-2 text-right">
                        <input type="number" step="any" min="0" max={d.jumlah_kirim} value={qtyMap[d.id] || ''} onChange={(e) => setQtyMap({ ...qtyMap, [d.id]: e.target.value })} className="w-24 px-2 py-1 text-sm border border-gray-300 rounded text-right" placeholder="0" />
                      </td>
                    )}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {isDikirim && (
            <div className="mt-4 pt-4 border-t flex justify-end">
              <button onClick={handleTerima} disabled={processing} className="px-6 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50">
                {processing ? 'Memproses...' : 'Konfirmasi Penerimaan'}
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

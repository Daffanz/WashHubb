import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getPenerimaan } from '../../../api/penerimaanStokOutlet';
import PageHeader from '../../../components/ui/PageHeader';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import { formatQty, formatDate } from '../../../utils/format';

export default function PenerimaanDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [penerimaan, setPenerimaan] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchData = useCallback(async () => {
    try { const res = await getPenerimaan(id); setPenerimaan(res.data.data); }
    catch { toast.error('Gagal memuat data'); navigate(-1); }
    finally { setLoading(false); }
  }, [id, navigate]);

  useEffect(() => { fetchData(); }, [fetchData]);

  if (loading) return <LoadingSpinner />;
  if (!penerimaan) return null;

  return (
    <div>
      <PageHeader title={`Penerimaan #${penerimaan.id}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Penerimaan Stok Outlet', to: '/operasional/penerimaan-stok-outlet' }, { label: 'Detail' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Penerimaan</h3>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Distribusi</dt><dd>#{penerimaan.distribusi?.id || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Outlet</dt><dd>{penerimaan.distribusi?.outlet || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Tanggal Kirim</dt><dd>{formatDate(penerimaan.distribusi?.tanggal_kirim)}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Tanggal Terima</dt><dd>{formatDate(penerimaan.tanggal_terima)}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Diterima Oleh</dt><dd>{penerimaan.user || '-'}</dd></div>
          </dl>
        </div>

        <div className="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Items Diterima</h3>
          <div className="overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead><tr className="border-b">
                <th className="text-left py-2 text-gray-500">Tipe</th>
                <th className="text-left py-2 text-gray-500">Item</th>
                <th className="text-right py-2 text-gray-500">Jumlah Kirim</th>
                <th className="text-right py-2 text-gray-500">Qty Diterima</th>
              </tr></thead>
              <tbody>
                {penerimaan.details?.map((d) => (
                  <tr key={d.id} className="border-b last:border-0">
                    <td className="py-2">
                      <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${d.tipe_item === 'mesin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}`}>
                        {d.tipe_item === 'mesin' ? 'Mesin' : 'Bahan Baku'}
                      </span>
                    </td>
                    <td className="py-2 font-medium">{d.tipe_item === 'mesin' ? d.mesin?.nama : d.bahan_baku?.nama || '-'}</td>
                    <td className="py-2 text-right">{formatQty(d.jumlah_kirim)}</td>
                    <td className="py-2 text-right font-semibold text-emerald-600">{formatQty(d.qty_diterima)}</td>
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

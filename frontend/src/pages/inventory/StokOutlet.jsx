import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getOutlet } from '../../api/outlets';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatQty } from '../../utils/format';

export default function StokOutlet() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [outlet, setOutlet] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchData = useCallback(async () => {
    try { const res = await getOutlet(id); setOutlet(res.data.data); }
    catch { toast.error('Gagal memuat data'); navigate(-1); }
    finally { setLoading(false); }
  }, [id, navigate]);

  useEffect(() => { fetchData(); }, [fetchData]);

  if (loading) return <LoadingSpinner />;
  if (!outlet) return null;

  return (
    <div>
      <PageHeader title={`Stok Outlet: ${outlet.nama}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Stok Outlet' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Stok Bahan Baku</h3>
          {outlet.stok_bahan_bakus?.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm">
                <thead><tr className="border-b">
                  <th className="text-left py-2 text-gray-500">Bahan Baku</th>
                  <th className="text-right py-2 text-gray-500">Stok</th>
                </tr></thead>
                <tbody>
                  {outlet.stok_bahan_bakus.map((s) => (
                    <tr key={s.id} className="border-b last:border-0">
                      <td className="py-2 font-medium">{s.bahan_baku?.nama || '-'}</td>
                      <td className="py-2 text-right">{formatQty(s.stok_saat_ini)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="text-sm text-gray-500">Belum ada stok bahan baku</p>
          )}
        </div>

        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Stok Mesin</h3>
          {outlet.stok_mesins?.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm">
                <thead><tr className="border-b">
                  <th className="text-left py-2 text-gray-500">Mesin</th>
                  <th className="text-right py-2 text-gray-500">Stok</th>
                </tr></thead>
                <tbody>
                  {outlet.stok_mesins.map((s) => (
                    <tr key={s.id} className="border-b last:border-0">
                      <td className="py-2 font-medium">{s.mesin?.nama || '-'}</td>
                      <td className="py-2 text-right">{formatQty(s.stok_saat_ini)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="text-sm text-gray-500">Belum ada stok mesin</p>
          )}
        </div>
      </div>
    </div>
  );
}

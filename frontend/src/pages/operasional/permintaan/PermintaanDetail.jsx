import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getPermintaan, validatePermintaan } from '../../../api/permintaanStok';
import PageHeader from '../../../components/ui/PageHeader';
import StatusBadge from '../../../components/ui/StatusBadge';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import { formatQty, formatDate } from '../../../utils/format';

export default function PermintaanDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [permintaan, setPermintaan] = useState(null);
  const [loading, setLoading] = useState(true);
  const [decisions, setDecisions] = useState({});
  const [validating, setValidating] = useState(false);

  const fetchData = useCallback(async () => {
    try { const res = await getPermintaan(id); setPermintaan(res.data.data); }
    catch { toast.error('Gagal memuat data'); navigate(-1); }
    finally { setLoading(false); }
  }, [id, navigate]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const isDiajukan = permintaan?.status?.kode === 'diajukan';

  const setDecision = (detailId, field, val) => {
    setDecisions((prev) => ({ ...prev, [detailId]: { ...prev[detailId], [field]: val } }));
  };

  const handleValidate = async () => {
    const items = [];
    for (const d of permintaan.details) {
      const dec = decisions[d.id];
      if (!dec?.status) { toast.error(`Item "${d.bahan_baku?.nama}" belum divalidasi`); return; }
      if ((dec.status === 'ditolak' || dec.status === 'disetujui_sebagian') && !dec.alasan) { toast.error(`Item "${d.bahan_baku?.nama}" wajib isi alasan`); return; }
      if (dec.status === 'disetujui_sebagian' && (!dec.jumlah_disetujui || parseFloat(dec.jumlah_disetujui) <= 0)) { toast.error(`Item "${d.bahan_baku?.nama}" wajib isi jumlah disetujui`); return; }
      items.push({
        detail_id: d.id,
        status: dec.status,
        jumlah_disetujui: dec.status === 'disetujui' ? d.jumlah_diminta : parseFloat(dec.jumlah_disetujui || 0),
        alasan: dec.alasan || null,
      });
    }

    setValidating(true);
    try {
      await validatePermintaan(id, { items });
      toast.success('Validasi berhasil');
      await fetchData();
      setDecisions({});
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal validasi'); }
    setValidating(false);
  };

  if (loading) return <LoadingSpinner />;
  if (!permintaan) return null;

  return (
    <div>
      <PageHeader title={`Permintaan #${permintaan.id}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Permintaan Stok', to: '/operasional/permintaan-stok' }, { label: 'Detail' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Permintaan</h3>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Outlet</dt><dd>{permintaan.outlet?.nama || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Tanggal</dt><dd>{formatDate(permintaan.tanggal)}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={permintaan.status?.label} color={{ diajukan: 'yellow', disetujui: 'green', disetujui_sebagian: 'amber', ditolak: 'red' }[permintaan.status?.kode] || 'gray'} /></dd></div>
          </dl>
        </div>

        <div className="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Items ({permintaan.details?.length || 0})</h3>
          <div className="overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead><tr className="border-b">
                <th className="text-left py-2 text-gray-500">Tipe</th>
                <th className="text-left py-2 text-gray-500">Item</th>
                <th className="text-right py-2 text-gray-500">Diminta</th>
                <th className="text-right py-2 text-gray-500">Disetujui</th>
                <th className="text-center py-2 text-gray-500">Status</th>
                {isDiajukan && <th className="text-center py-2 text-gray-500">Validasi</th>}
              </tr></thead>
              <tbody>
                {permintaan.details?.map((d) => {
                  const dec = decisions[d.id] || {};
                  const itemName = d.tipe_item === 'mesin' ? d.mesin?.nama : d.bahan_baku?.nama;
                  return (
                    <tr key={d.id} className="border-b last:border-0">
                      <td className="py-2">
                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${d.tipe_item === 'mesin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}`}>
                          {d.tipe_item === 'mesin' ? 'Mesin' : 'Bahan Baku'}
                        </span>
                      </td>
                      <td className="py-2 font-medium">{itemName || '-'}</td>
                      <td className="py-2 text-right">{formatQty(d.jumlah_diminta)}</td>
                      <td className="py-2 text-right">{d.jumlah_disetujui !== null ? formatQty(d.jumlah_disetujui) : '-'}</td>
                      <td className="py-2 text-center"><StatusBadge status={d.status?.label || 'Diajukan'} color={{ diajukan: 'yellow', disetujui: 'green', disetujui_sebagian: 'amber', ditolak: 'red' }[d.status?.kode] || 'yellow'} /></td>
                      {isDiajukan && (
                        <td className="py-2">
                          <div className="flex flex-col gap-2">
                            <div className="flex gap-1 justify-center">
                              {[['disetujui', 'Setuju', 'emerald'], ['disetujui_sebagian', 'Sebagian', 'amber'], ['ditolak', 'Tolak', 'red']].map(([val, lbl, clr]) => (
                                <button key={val} onClick={() => setDecision(d.id, 'status', val)} className={`text-xs font-medium px-2 py-1 rounded ${dec.status === val ? `bg-${clr}-600 text-white` : `bg-gray-100 text-gray-600 hover:bg-${clr}-50`}`}>{lbl}</button>
                              ))}
                            </div>
                            {dec.status === 'disetujui_sebagian' && <input type="number" placeholder="Jumlah disetujui" value={dec.jumlah_disetujui || ''} onChange={(e) => setDecision(d.id, 'jumlah_disetujui', e.target.value)} className="w-full px-2 py-1 text-xs border border-gray-300 rounded" step="any" min="0" />}
                            {(dec.status === 'ditolak' || dec.status === 'disetujui_sebagian') && <input type="text" placeholder="Alasan wajib" value={dec.alasan || ''} onChange={(e) => setDecision(d.id, 'alasan', e.target.value)} className="w-full px-2 py-1 text-xs border border-gray-300 rounded" />}
                          </div>
                        </td>
                      )}
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
          {isDiajukan && (
            <div className="mt-4 pt-4 border-t flex justify-end">
              <button onClick={handleValidate} disabled={validating} className="px-6 py-2 text-sm font-medium text-white bg-wash-700 rounded-lg hover:bg-wash-800 disabled:opacity-50">
                {validating ? 'Memproses...' : 'Kirim Validasi'}
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import StatusBadge from '../../components/ui/StatusBadge';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatRupiah } from '../../utils/format';
import { validatePurchaseOrder } from '../../api/purchaseOrders';

export default function PurchaseOrderDetail() {
  const { id } = useParams();
  const [po, setPo] = useState(null);
  const [loading, setLoading] = useState(true);
  const [validating, setValidating] = useState(false);
  const [itemDecisions, setItemDecisions] = useState({});

  const refresh = async () => {
    const res = await fetch(`/api/procurement/purchase-orders/${id}`, { headers: { Authorization: `Bearer ${localStorage.getItem('washhub_token')}` } });
    setPo((await res.json()).data);
  };

  useEffect(() => { refresh().catch(() => toast.error('Gagal memuat data')).finally(() => setLoading(false)); }, [id]);

  const isDikirim = po?.status?.kode === 'dikirim';

  const setDecision = (itemId, field, value) => {
    setItemDecisions(prev => ({ ...prev, [itemId]: { ...prev[itemId], [field]: value } }));
  };

  // UC-31: Supplier validasi per-item
  const handleValidate = async () => {
    const allItems = [...(po.items_bahan_baku || []), ...(po.items_mesin || [])];
    const payload = { items: [] };

    for (const item of allItems) {
      const d = itemDecisions[item.id];
      if (!d || !d.status_kode) { toast.error(`Item "${item.nama}" belum divalidasi`); return; }
      if ((d.status_kode === 'ditolak' || d.status_kode === 'disetujui_sebagian') && !d.alasan) { toast.error(`Item "${item.nama}" wajib isi alasan`); return; }
      if (d.status_kode === 'disetujui_sebagian' && (!d.qty_disetujui || parseFloat(d.qty_disetujui) <= 0)) { toast.error(`Item "${item.nama}" wajib isi qty disetujui`); return; }

      payload.items.push({
        item_id: item.id,
        status_kode: d.status_kode,
        qty_disetujui: d.status_kode === 'disetujui' ? item.jumlah : parseFloat(d.qty_disetujui || 0),
        alasan: d.alasan || null,
      });
    }

    setValidating(true);
    try {
      await validatePurchaseOrder(id, payload);
      toast.success('Validasi berhasil. PO status otomatis diupdate.');
      await refresh();
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal validasi'); }
    setValidating(false);
  };

  if (loading) return <LoadingSpinner />;
  if (!po) return null;

  const allItems = [...(po.items_bahan_baku || []), ...(po.items_mesin || [])];

  return (
    <div>
      <PageHeader title={`PO: ${po.nomor_po}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'PO', to: '/procurement/purchase-orders' }, { label: 'Detail' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info PO</h3>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Supplier</dt><dd>{po.supplier?.nama || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Dibuat Oleh</dt><dd>{po.dibuat_oleh?.nama || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Jenis</dt><dd className="capitalize">{po.jenis_po?.replace('_', ' ')}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={po.status?.label} color={
              po.status?.kode === 'selesai' ? 'green' : po.status?.kode === 'ditolak' ? 'red' :
              po.status?.kode === 'disetujui' ? 'green' : po.status?.kode === 'dikirim' ? 'blue' : 'yellow'
            } /></dd></div>
            <div className="flex justify-between pt-2 border-t"><dt className="font-semibold">Total Nilai</dt><dd className="font-bold">{formatRupiah(po.total_nilai)}</dd></div>
          </dl>

          {/* Distribusi info */}
          {po.distribusi?.length > 0 && (
            <div className="mt-4 pt-4 border-t">
              <h4 className="text-xs font-medium text-gray-500 mb-2">Distribusi</h4>
              {po.distribusi.map(d => (
                <Link key={d.id} to={`/procurement/distributions/${d.id}`} className="flex justify-between items-center text-xs p-2 bg-gray-50 rounded hover:bg-gray-100">
                  <span className="font-mono">{d.nomor_distribusi}</span>
                  <StatusBadge status={d.status} color={d.status === 'diterima' ? 'green' : 'blue'} />
                </Link>
              ))}
            </div>
          )}
        </div>

        <div className="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Items ({allItems.length})</h3>
          <div className="overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead><tr className="border-b">
                <th className="text-left py-2 text-gray-500">Item</th>
                <th className="text-right py-2 text-gray-500">Qty</th>
                <th className="text-right py-2 text-gray-500">Harga</th>
                <th className="text-right py-2 text-gray-500">Qty Disetujui</th>
                <th className="text-center py-2 text-gray-500">Status</th>
                {isDikirim && <th className="text-center py-2 text-gray-500">Validasi</th>}
              </tr></thead>
              <tbody>
                {allItems.map((item) => {
                  const d = itemDecisions[item.id] || {};
                  return (
                    <tr key={item.id} className="border-b last:border-0">
                      <td className="py-2 font-medium">{item.nama}</td>
                      <td className="py-2 text-right">{item.jumlah}</td>
                      <td className="py-2 text-right">{formatRupiah(item.harga_satuan)}</td>
                      <td className="py-2 text-right">{item.qty_disetujui ?? '-'}</td>
                      <td className="py-2 text-center"><StatusBadge status={item.status || 'diajukan'} color={item.status === 'disetujui' ? 'green' : item.status === 'ditolak' ? 'red' : 'yellow'} /></td>
                      {isDikirim && (
                        <td className="py-2">
                          <div className="flex flex-col gap-2">
                            <div className="flex gap-1 justify-center">
                              <button onClick={() => setDecision(item.id, 'status_kode', 'disetujui')} className={`text-xs font-medium px-2 py-1 rounded ${d.status_kode === 'disetujui' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-emerald-50'}`}>Setuju</button>
                              <button onClick={() => setDecision(item.id, 'status_kode', 'disetujui_sebagian')} className={`text-xs font-medium px-2 py-1 rounded ${d.status_kode === 'disetujui_sebagian' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-amber-50'}`}>Sebagian</button>
                              <button onClick={() => setDecision(item.id, 'status_kode', 'ditolak')} className={`text-xs font-medium px-2 py-1 rounded ${d.status_kode === 'ditolak' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-red-50'}`}>Tolak</button>
                            </div>
                            {d.status_kode === 'disetujui_sebagian' && (
                              <input type="number" placeholder="Qty disetujui" value={d.qty_disetujui || ''} onChange={(e) => setDecision(item.id, 'qty_disetujui', e.target.value)} className="w-full px-2 py-1 text-xs border border-gray-300 rounded" step="any" min="0" />
                            )}
                            {(d.status_kode === 'ditolak' || d.status_kode === 'disetujui_sebagian') && (
                              <input type="text" placeholder="Alasan wajib" value={d.alasan || ''} onChange={(e) => setDecision(item.id, 'alasan', e.target.value)} className="w-full px-2 py-1 text-xs border border-gray-300 rounded" />
                            )}
                          </div>
                        </td>
                      )}
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>

          {isDikirim && (
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

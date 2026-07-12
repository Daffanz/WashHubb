import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import StatusBadge from '../../components/ui/StatusBadge';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatRupiah } from '../../utils/format';

export default function PurchaseOrderDetail() {
  const { id } = useParams();
  const [po, setPo] = useState(null);
  const [loading, setLoading] = useState(true);
  const [validating, setValidating] = useState(null);

  useEffect(() => {
    fetch(`/api/procurement/purchase-orders/${id}`, { headers: { Authorization: `Bearer ${localStorage.getItem('washhub_token')}` } })
      .then(r => r.json()).then(d => setPo(d.data))
      .catch(() => toast.error('Gagal memuat data'))
      .finally(() => setLoading(false));
  }, [id]);

  const handleValidateItem = async (itemId, statusKode, qtyDisetujui, alasan) => {
    setValidating(itemId);
    try {
      const res = await fetch(`/api/procurement/purchase-orders/${id}/items/${itemId}/validate`, {
        method: 'PATCH', headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${localStorage.getItem('washhub_token')}` },
        body: JSON.stringify({ status_kode: statusKode, qty_disetujui: qtyDisetujui, alasan }),
      });
      if (!res.ok) { const d = await res.json(); throw new Error(d.message); }
      toast.success('Item berhasil divalidasi');
      const refreshed = await fetch(`/api/procurement/purchase-orders/${id}`, { headers: { Authorization: `Bearer ${localStorage.getItem('washhub_token')}` } });
      setPo((await refreshed.json()).data);
    } catch (err) { toast.error(err.message || 'Gagal validasi'); }
    setValidating(null);
  };

  if (loading) return <LoadingSpinner />;
  if (!po) return null;

  const allItems = [...(po.items_bahan_baku || []), ...(po.items_mesin || [])];
  const isDikirim = po.status?.kode === 'dikirim';

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
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={po.status?.label} color={po.status?.kode === 'disetujui' ? 'green' : po.status?.kode === 'ditolak' ? 'red' : 'blue'} /></dd></div>
            <div className="flex justify-between pt-2 border-t"><dt className="font-semibold">Total Nilai</dt><dd className="font-bold">{formatRupiah(po.total_nilai)}</dd></div>
          </dl>
        </div>
        <div className="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Items ({allItems.length})</h3>
          <div className="overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead><tr className="border-b"><th className="text-left py-2 text-gray-500">Item</th><th className="text-right py-2 text-gray-500">Qty</th><th className="text-right py-2 text-gray-500">Harga</th><th className="text-right py-2 text-gray-500">Subtotal</th><th className="text-center py-2 text-gray-500">Status</th>{isDikirim && <th className="text-center py-2 text-gray-500">Aksi</th>}</tr></thead>
              <tbody>
                {allItems.map((item) => (
                  <tr key={item.id} className="border-b last:border-0">
                    <td className="py-2 font-medium">{item.nama}</td>
                    <td className="py-2 text-right">{item.jumlah}</td>
                    <td className="py-2 text-right">{formatRupiah(item.harga_satuan)}</td>
                    <td className="py-2 text-right font-medium">{formatRupiah(item.jumlah * item.harga_satuan)}</td>
                    <td className="py-2 text-center"><StatusBadge status={item.status || 'diajukan'} color={item.status === 'disetujui' ? 'green' : item.status === 'ditolak' ? 'red' : 'yellow'} /></td>
                    {isDikirim && (
                      <td className="py-2 text-center">
                        {item.status !== 'disetujui' && item.status !== 'ditolak' && (
                          <div className="flex gap-1 justify-center">
                            <button onClick={() => handleValidateItem(item.id, 'disetujui', item.jumlah, '')} disabled={validating === item.id} className="text-xs text-emerald-600 hover:text-emerald-800 font-medium px-2 py-1 rounded hover:bg-emerald-50">Setuju</button>
                            <button onClick={() => { const qty = prompt('Qty disetujui:', item.jumlah); if (qty !== null) handleValidateItem(item.id, 'disetujui_sebagian', parseFloat(qty), prompt('Alasan:') || ''); }} disabled={validating === item.id} className="text-xs text-yellow-600 hover:text-yellow-800 font-medium px-2 py-1 rounded hover:bg-yellow-50">Sebagian</button>
                            <button onClick={() => { const alasan = prompt('Alasan penolakan:'); if (alasan) handleValidateItem(item.id, 'ditolak', 0, alasan); }} disabled={validating === item.id} className="text-xs text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50">Tolak</button>
                          </div>
                        )}
                      </td>
                    )}
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

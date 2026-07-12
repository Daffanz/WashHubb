import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getReturn, kirimPenggantiReturn, confirmReturn } from '../../api/returns';
import PageHeader from '../../components/ui/PageHeader';
import StatusBadge from '../../components/ui/StatusBadge';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatQty, formatDateTime } from '../../utils/format';
import { useAuth } from '../../hooks/useAuth';

export default function ReturnDetail() {
  const { id } = useParams();
  const { user } = useAuth();
  const isSupplier = user?.role?.kode === 'supplier';
  const [retur, setRetur] = useState(null);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState(false);
  const [penggantiItems, setPenggantiItems] = useState({ bb: [], mesin: [] });

  const refresh = async () => {
    const res = await getReturn(id);
    setRetur(res.data.data);

    // Init pengganti form untuk supplier
    if (res.data.data.status?.kode === 'menunggu_pengganti' && isSupplier) {
      setPenggantiItems({
        bb: (res.data.data.detail_bahan_baku || []).map(d => ({
          retur_detail_id: d.id, qty_pengganti: d.qty_retur, nama: `BB #${d.id}`, qty_retur: d.qty_retur,
        })),
        mesin: (res.data.data.detail_mesin || []).map(d => ({
          retur_detail_id: d.id, qty_pengganti: d.qty_retur, nama: `Mesin #${d.id}`, qty_retur: d.qty_retur,
        })),
      });
    }
  };

  useEffect(() => { refresh().catch(() => toast.error('Gagal memuat data')).finally(() => setLoading(false)); }, [id]);

  // Supplier kirim pengganti
  const handleKirimPengganti = async () => {
    setProcessing(true);
    try {
      const payload = {};
      if (penggantiItems.bb.length) {
        payload.items_bahan_baku = penggantiItems.bb.map(i => ({
          retur_detail_id: i.retur_detail_id,
          qty_pengganti: parseFloat(i.qty_pengganti),
        }));
      }
      if (penggantiItems.mesin.length) {
        payload.items_mesin = penggantiItems.mesin.map(i => ({
          retur_detail_id: i.retur_detail_id,
          qty_pengganti: parseInt(i.qty_pengganti),
        }));
      }
      await kirimPenggantiReturn(id, payload);
      toast.success('Pengganti berhasil dikirim. Stok supplier berkurang.');
      await refresh();
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setProcessing(false);
  };

  // Tim Pengadaan konfirmasi
  const handleConfirm = async (status) => {
    setProcessing(true);
    try {
      const payload = { status };
      if (status === 'selesai') {
        payload.items_bahan_baku = (retur.detail_bahan_baku || []).map(d => ({
          retur_detail_id: d.id, qty_pengganti: d.qty_pengganti || 0,
        }));
        payload.items_mesin = (retur.detail_mesin || []).map(d => ({
          retur_detail_id: d.id, qty_pengganti: d.qty_pengganti || 0,
        }));
      }
      await confirmReturn(id, payload);
      toast.success(status === 'selesai' ? 'Retur selesai. Stok perusahaan bertambah.' : 'Retur ditolak. Supplier harus kirim ulang.');
      await refresh();
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setProcessing(false);
  };

  if (loading) return <LoadingSpinner />;
  if (!retur) return null;

  const sk = retur.status?.kode;

  return (
    <div>
      <PageHeader title={`Retur #${retur.id}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Retur', to: '/procurement/returns' }, { label: 'Detail' }]} />

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {/* Info */}
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Retur</h3>
          <dl className="space-y-3 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">PO</dt><dd className="font-mono">{retur.po?.nomor_po || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Supplier</dt><dd>{retur.po?.supplier || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Tanggal</dt><dd>{formatDateTime(retur.tanggal_retur)}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={retur.status?.label} color={sk === 'selesai' ? 'green' : sk === 'pengganti_dikirim' ? 'blue' : 'yellow'} /></dd></div>
          </dl>
        </div>

        {/* Aksi */}
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Aksi</h3>
          {sk === 'menunggu_pengganti' && isSupplier && (
            <div>
              <p className="text-sm text-gray-500 mb-3">Siapkan dan kirim barang pengganti. Stok supplier akan berkurang otomatis.</p>
              <button onClick={handleKirimPengganti} disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {processing ? 'Mengirim...' : 'Kirim Pengganti'}
              </button>
            </div>
          )}
          {sk === 'pengganti_dikirim' && !isSupplier && (
            <div>
              <p className="text-sm text-gray-500 mb-3">Barang pengganti sudah dikirim supplier. Periksa kondisi fisik.</p>
              <div className="flex gap-3">
                <button onClick={() => handleConfirm('selesai')} disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50">
                  {processing ? 'Memproses...' : 'Sesuai (Selesai)'}
                </button>
                <button onClick={() => handleConfirm('ditolak')} disabled={processing} className="px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 disabled:opacity-50">
                  {processing ? 'Memproses...' : 'Tolak (Kirim Ulang)'}
                </button>
              </div>
            </div>
          )}
          {sk === 'selesai' && <p className="text-sm text-emerald-600 font-medium">Retur selesai. Barang pengganti sudah diterima.</p>}
          {sk === 'menunggu_pengganti' && !isSupplier && <p className="text-sm text-amber-600 font-medium">Menunggu supplier mengirim barang pengganti.</p>}
          {sk === 'pengganti_dikirim' && isSupplier && <p className="text-sm text-blue-600 font-medium">Barang pengganti sudah dikirim. Menunggu konfirmasi Tim Pengadaan.</p>}
        </div>
      </div>

      {/* Detail Items */}
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h3 className="text-sm font-semibold text-gray-900 mb-4">Detail Retur</h3>
        {retur.detail_bahan_baku?.length > 0 && (
          <div className="mb-4">
            <h4 className="text-xs font-medium text-gray-500 mb-2">Bahan Baku</h4>
            <table className="min-w-full text-sm">
              <thead><tr className="border-b">
                <th className="text-left py-2 text-gray-500">Item</th>
                <th className="text-right py-2 text-gray-500">Qty Retur</th>
                <th className="text-right py-2 text-gray-500">Qty Pengganti</th>
                <th className="text-left py-2 text-gray-500">Alasan</th>
                <th className="text-center py-2 text-gray-500">Status</th>
              </tr></thead>
              <tbody>
                {retur.detail_bahan_baku.map((d) => (
                  <tr key={d.id} className="border-b last:border-0">
                    <td className="py-2 font-medium">BB #{d.id}</td>
                    <td className="py-2 text-right">{formatQty(d.qty_retur)}</td>
                    <td className="py-2 text-right font-semibold">{d.qty_pengganti ? formatQty(d.qty_pengganti) : '-'}</td>
                    <td className="py-2 text-gray-500">{d.alasan}</td>
                    <td className="py-2 text-center"><StatusBadge status={d.status?.label} color={d.status?.kode === 'selesai' ? 'green' : 'yellow'} /></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
        {retur.detail_mesin?.length > 0 && (
          <div>
            <h4 className="text-xs font-medium text-gray-500 mb-2">Mesin</h4>
            <table className="min-w-full text-sm">
              <thead><tr className="border-b">
                <th className="text-left py-2 text-gray-500">Item</th>
                <th className="text-right py-2 text-gray-500">Qty Retur</th>
                <th className="text-right py-2 text-gray-500">Qty Pengganti</th>
                <th className="text-left py-2 text-gray-500">Alasan</th>
                <th className="text-center py-2 text-gray-500">Status</th>
              </tr></thead>
              <tbody>
                {retur.detail_mesin.map((d) => (
                  <tr key={d.id} className="border-b last:border-0">
                    <td className="py-2 font-medium">Mesin #{d.id}</td>
                    <td className="py-2 text-right">{formatQty(d.qty_retur)}</td>
                    <td className="py-2 text-right font-semibold">{d.qty_pengganti ? formatQty(d.qty_pengganti) : '-'}</td>
                    <td className="py-2 text-gray-500">{d.alasan}</td>
                    <td className="py-2 text-center"><StatusBadge status={d.status?.label} color={d.status?.kode === 'selesai' ? 'green' : 'yellow'} /></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}

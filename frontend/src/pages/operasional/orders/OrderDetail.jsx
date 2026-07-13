import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getOrder, updateOrder } from '../../../api/orderCucian';
import PageHeader from '../../../components/ui/PageHeader';
import StatusBadge from '../../../components/ui/StatusBadge';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import { formatRupiah, formatDateTime } from '../../../utils/format';

export default function OrderDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState(false);
  const [alasan, setAlasan] = useState('');

  const fetchOrder = async () => {
    try { const res = await getOrder(id); setOrder(res.data.data); }
    catch { toast.error('Gagal memuat data'); navigate(-1); }
    finally { setLoading(false); }
  };

  useEffect(() => { fetchOrder(); }, [id]);

  const handleAction = async (status) => {
    if (status === 'dibatalkan' && !alasan.trim()) { toast.error('Alasan pembatalan wajib diisi'); return; }
    setProcessing(true);
    try {
      const payload = { status };
      if (status === 'dibatalkan') payload.alasan_pembatalan = alasan;
      await updateOrder(id, payload);
      toast.success(`Order berhasil ${status === 'selesai' ? 'diselesaikan' : 'dibatalkan'}`);
      await fetchOrder();
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setProcessing(false);
  };

  if (loading) return <LoadingSpinner />;
  if (!order) return null;

  const isDiproses = order.status?.kode === 'diproses';

  return (
    <div>
      <PageHeader title={`Order #${order.id}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Orders', to: '/operasional/orders' }, { label: 'Detail' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Order</h3>
          <dl className="space-y-3 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Outlet</dt><dd className="font-medium">{order.outlet?.nama || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Layanan</dt><dd>{order.jenis_layanan?.nama || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Mesin</dt><dd>{order.mesin?.nama || '-'} {order.mesin?.nomor_seri ? `(${order.mesin.nomor_seri})` : ''}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Berat</dt><dd>{order.berat} kg</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Total Harga</dt><dd className="font-semibold text-wash-800">{formatRupiah(order.total_harga)}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={order.status?.label} color={order.status?.kode === 'selesai' ? 'green' : order.status?.kode === 'dibatalkan' ? 'red' : 'blue'} /></dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Waktu Masuk</dt><dd>{formatDateTime(order.waktu_masuk)}</dd></div>
            {order.estimasi_selesai && <div className="flex justify-between"><dt className="text-gray-500">Estimasi Selesai</dt><dd>{formatDateTime(order.estimasi_selesai)}</dd></div>}
            {order.waktu_selesai && <div className="flex justify-between"><dt className="text-gray-500">Waktu Selesai</dt><dd>{formatDateTime(order.waktu_selesai)}</dd></div>}
            {order.alasan_pembatalan && <div className="flex justify-between"><dt className="text-gray-500">Alasan Batal</dt><dd className="text-red-600">{order.alasan_pembatalan}</dd></div>}
          </dl>
        </div>

        {isDiproses && (
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 className="text-sm font-semibold text-gray-900 mb-4">Aksi</h3>
            <div className="space-y-4">
              <button onClick={() => handleAction('selesai')} disabled={processing} className="w-full px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50">
                {processing ? 'Memproses...' : 'Tandai Selesai'}
              </button>
              <div className="border-t pt-4">
                <p className="text-xs text-gray-500 mb-2">Batalkan order:</p>
                <textarea value={alasan} onChange={(e) => setAlasan(e.target.value)} placeholder="Alasan pembatalan (wajib)" className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg mb-2" rows={2} />
                <button onClick={() => handleAction('dibatalkan')} disabled={processing || !alasan.trim()} className="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                  Batalkan Order
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

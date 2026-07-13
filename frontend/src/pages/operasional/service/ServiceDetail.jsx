import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getJadwalService, validateJadwalService, completeJadwalService } from '../../../api/jadwalService';
import PageHeader from '../../../components/ui/PageHeader';
import StatusBadge from '../../../components/ui/StatusBadge';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import { formatDate } from '../../../utils/format';

export default function ServiceDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [jadwal, setJadwal] = useState(null);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState(false);

  const fetchData = useCallback(async () => {
    try { const res = await getJadwalService(id); setJadwal(res.data.data); }
    catch { toast.error('Gagal memuat data'); navigate(-1); }
    finally { setLoading(false); }
  }, [id, navigate]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const handleValidate = async (status) => {
    setProcessing(true);
    try {
      await validateJadwalService(id, { status });
      toast.success(`Service berhasil ${status === 'disetujui' ? 'disetujui' : 'ditolak'}`);
      await fetchData();
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setProcessing(false);
  };

  const handleComplete = async () => {
    setProcessing(true);
    try {
      await completeJadwalService(id);
      toast.success('Service selesai. Mesin kembali aktif.');
      await fetchData();
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setProcessing(false);
  };

  if (loading) return <LoadingSpinner />;
  if (!jadwal) return null;

  const isMenunggu = jadwal.status?.kode === 'menunggu_persetujuan';
  const isDisetujui = jadwal.status?.kode === 'disetujui';

  return (
    <div>
      <PageHeader title={`Service #${jadwal.id}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Service', to: '/operasional/jadwal-service' }, { label: 'Detail' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Service</h3>
          <dl className="space-y-3 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Mesin</dt><dd className="font-medium">{jadwal.detail_mesin?.mesin || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">No. Seri</dt><dd>{jadwal.detail_mesin?.nomor_seri || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Outlet</dt><dd>{jadwal.outlet?.nama || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Diajukan Oleh</dt><dd>{jadwal.user?.nama || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Tanggal Pengajuan</dt><dd>{formatDate(jadwal.tanggal_pengajuan)}</dd></div>
            {jadwal.tanggal_service && <div className="flex justify-between"><dt className="text-gray-500">Tanggal Service</dt><dd>{formatDate(jadwal.tanggal_service)}</dd></div>}
            <div className="flex justify-between"><dt className="text-gray-500">Menunggu Mesin Bebas</dt><dd>{jadwal.menunggu_mesin_bebas ? <span className="text-amber-600 font-medium">Ya</span> : 'Tidak'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={jadwal.status?.label} color={{ menunggu_persetujuan: 'yellow', disetujui: 'green', ditolak: 'red', selesai: 'green' }[jadwal.status?.kode] || 'gray'} /></dd></div>
          </dl>
        </div>

        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Deskripsi</h3>
          <p className="text-sm text-gray-700 whitespace-pre-wrap">{jadwal.deskripsi || '-'}</p>

          {isMenunggu && (
            <div className="mt-6 pt-4 border-t flex gap-3">
              <button onClick={() => handleValidate('disetujui')} disabled={processing} className="flex-1 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50">
                {processing ? '...' : 'Setujui'}
              </button>
              <button onClick={() => handleValidate('ditolak')} disabled={processing} className="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                {processing ? '...' : 'Tolak'}
              </button>
            </div>
          )}

          {isDisetujui && (
            <div className="mt-6 pt-4 border-t">
              <button onClick={handleComplete} disabled={processing} className="w-full px-4 py-2 text-sm font-medium text-white bg-wash-700 rounded-lg hover:bg-wash-800 disabled:opacity-50">
                {processing ? 'Memproses...' : 'Tandai Service Selesai'}
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

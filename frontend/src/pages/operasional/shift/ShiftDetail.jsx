import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getJadwalShift } from '../../../api/jadwalShift';
import PageHeader from '../../../components/ui/PageHeader';
import StatusBadge from '../../../components/ui/StatusBadge';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import { formatDate } from '../../../utils/format';
import { HARI_OPTIONS } from '../../../utils/constants';

export default function ShiftDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [jadwal, setJadwal] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getJadwalShift(id).then((res) => setJadwal(res.data.data)).catch(() => { toast.error('Gagal memuat data'); navigate(-1); }).finally(() => setLoading(false));
  }, [id, navigate]);

  if (loading) return <LoadingSpinner />;
  if (!jadwal) return null;

  const isBelumBerjalan = jadwal.status?.kode === 'belum_berjalan';
  const hariLabels = Object.fromEntries(HARI_OPTIONS.map((h) => [h.value, h.label]));

  // Group shifts by day
  const grouped = {};
  (jadwal.details || []).forEach((d) => {
    if (!grouped[d.hari]) grouped[d.hari] = [];
    grouped[d.hari].push(d);
  });

  return (
    <div>
      <PageHeader title={`Shift #${jadwal.id}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Shift', to: '/operasional/jadwal-shift' }, { label: 'Detail' }]}
        actionLabel={isBelumBerjalan ? 'Edit' : undefined} actionTo={isBelumBerjalan ? `/operasional/jadwal-shift/${id}/edit` : undefined} />
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Jadwal</h3>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Outlet</dt><dd>{jadwal.outlet?.nama || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Minggu Mulai</dt><dd>{formatDate(jadwal.minggu_mulai)}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={jadwal.status?.label} color={{ belum_berjalan: 'gray', berjalan: 'blue', selesai: 'green' }[jadwal.status?.kode] || 'gray'} /></dd></div>
          </dl>
        </div>

        <div className="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Jadwal per Hari</h3>
          {Object.keys(grouped).length === 0 ? (
            <p className="text-sm text-gray-500">Belum ada shift</p>
          ) : (
            <div className="space-y-4">
              {Object.entries(grouped).map(([hari, items]) => (
                <div key={hari} className="p-3 bg-gray-50 rounded-lg">
                  <h4 className="text-sm font-semibold text-gray-800 mb-2">{hariLabels[hari] || hari}</h4>
                  <div className="space-y-1">
                    {items.map((item) => (
                      <div key={item.id} className="flex items-center gap-3 text-sm">
                        <span className="font-medium">{item.user?.nama || '-'}</span>
                        <span className="text-gray-500">{item.jam_mulai} - {item.jam_selesai}</span>
                      </div>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

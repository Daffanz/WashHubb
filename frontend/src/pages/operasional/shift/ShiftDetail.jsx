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

  const hariLabels = Object.fromEntries(HARI_OPTIONS.map((h) => [h.value, h.label]));

  // Group shifts by day
  const grouped = {};
  (jadwal.details || []).forEach((d) => {
    if (!grouped[d.hari]) grouped[d.hari] = [];
    grouped[d.hari].push(d);
  });

  // Cek apakah semua detail sudah selesai
  const semuaSelesai = jadwal.details?.every((d) => d.status?.kode === 'selesai');
  const adaYangBerjalan = jadwal.details?.some((d) => d.status?.kode === 'berjalan');

  return (
    <div>
      <PageHeader title={`Shift #${jadwal.id}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Shift', to: '/operasional/jadwal-shift' }, { label: 'Detail' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Jadwal</h3>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Outlet</dt><dd>{jadwal.outlet?.nama || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Minggu Mulai</dt><dd>{formatDate(jadwal.minggu_mulai)}</dd></div>
            <div className="flex justify-between">
              <dt className="text-gray-500">Status Keseluruhan</dt>
              <dd>
                <StatusBadge
                  status={semuaSelesai ? 'Selesai' : adaYangBerjalan ? 'Berjalan' : 'Belum Berjalan'}
                  color={semuaSelesai ? 'green' : adaYangBerjalan ? 'blue' : 'gray'}
                />
              </dd>
            </div>
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
                  <div className="space-y-2">
                    {items.map((item) => (
                      <div key={item.id} className="flex items-center justify-between text-sm">
                        <div className="flex items-center gap-3">
                          <span className="font-medium">{item.nama_karyawan || '-'}</span>
                          <span className="text-gray-500">{item.jam_mulai} - {item.jam_selesai}</span>
                        </div>
                        <StatusBadge
                          status={item.status?.label || 'Belum Berjalan'}
                          color={{ belum_berjalan: 'gray', berjalan: 'blue', selesai: 'green' }[item.status?.kode] || 'gray'}
                        />
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

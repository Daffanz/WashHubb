import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getOutlet } from '../../../api/outlets';
import PageHeader from '../../../components/ui/PageHeader';
import StatusBadge from '../../../components/ui/StatusBadge';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';

export default function OutletDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [outlet, setOutlet] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getOutlet(id).then((res) => setOutlet(res.data.data)).catch(() => { toast.error('Gagal memuat data'); navigate(-1); }).finally(() => setLoading(false));
  }, [id, navigate]);

  if (loading) return <LoadingSpinner />;
  if (!outlet) return null;

  return (
    <div>
      <PageHeader title={`Outlet: ${outlet.nama}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Franchise', to: '/franchise/outlets' }, { label: 'Detail' }]}
        actionLabel="Edit" actionTo={`/franchise/outlets/${id}/edit`} />
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Outlet</h3>
          <dl className="space-y-3 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Nama</dt><dd className="font-medium">{outlet.nama}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Kode</dt><dd className="font-mono">{outlet.kode_outlet}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Alamat</dt><dd className="text-right max-w-xs">{outlet.alamat}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Franchise</dt><dd>{outlet.franchise?.user?.nama || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={outlet.status?.label} color={outlet.status?.kode === 'aktif' ? 'green' : 'red'} /></dd></div>
          </dl>
        </div>
        {outlet.users?.length > 0 && (
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 className="text-sm font-semibold text-gray-900 mb-4">Staf Outlet</h3>
            <ul className="space-y-2">
              {outlet.users.map((u) => (
                <li key={u.id} className="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                  <div className="w-8 h-8 rounded-full bg-wash-100 flex items-center justify-center text-sm font-bold text-wash-700">{u.nama?.charAt(0)}</div>
                  <span className="text-sm font-medium">{u.nama}</span>
                </li>
              ))}
            </ul>
          </div>
        )}
      </div>
    </div>
  );
}

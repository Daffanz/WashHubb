import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getUser } from '../../api/users';
import PageHeader from '../../components/ui/PageHeader';
import StatusBadge from '../../components/ui/StatusBadge';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatDate } from '../../utils/format';

export default function UserDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getUser(id).then((res) => setUser(res.data.data)).catch(() => { toast.error('Gagal memuat data'); navigate(-1); }).finally(() => setLoading(false));
  }, [id, navigate]);

  if (loading) return <LoadingSpinner />;
  if (!user) return null;

  return (
    <div>
      <PageHeader title={`User: ${user.name}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Users', to: '/users' }, { label: 'Detail' }]} />
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-lg">
        <dl className="space-y-3 text-sm">
          <div className="flex justify-between"><dt className="text-gray-500">Nama</dt><dd className="font-medium">{user.name}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Email</dt><dd>{user.email}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Role</dt><dd>{user.roles?.join(', ') || '-'}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={user.status} /></dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Dibuat</dt><dd>{formatDate(user.created_at)}</dd></div>
        </dl>
      </div>
    </div>
  );
}

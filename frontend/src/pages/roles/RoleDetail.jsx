import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getRole } from '../../api/roles';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';

export default function RoleDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [role, setRole] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getRole(id).then((res) => setRole(res.data.data)).catch(() => { toast.error('Gagal memuat data'); navigate(-1); }).finally(() => setLoading(false));
  }, [id, navigate]);

  if (loading) return <LoadingSpinner />;
  if (!role) return null;

  return (
    <div>
      <PageHeader title={`Role: ${role.name}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Roles', to: '/roles' }, { label: 'Detail' }]} />
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-lg mb-6">
        <dl className="space-y-3 text-sm">
          <div className="flex justify-between"><dt className="text-gray-500">Nama Role</dt><dd className="font-medium">{role.name}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Jumlah Permission</dt><dd>{role.permissions?.length || 0}</dd></div>
        </dl>
      </div>
      {role.permissions?.length > 0 && (
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-3">Permissions</h3>
          <div className="flex flex-wrap gap-2">
            {role.permissions.map((p, i) => (
              <span key={i} className="px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">{p.name || p}</span>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

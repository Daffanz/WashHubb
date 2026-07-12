import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getRole, createRole, updateRole } from '../../api/roles';
import { getPermissions } from '../../api/permissions';
import PageHeader from '../../components/ui/PageHeader';
import FormInput from '../../components/ui/FormInput';
import FormActions from '../../components/ui/FormActions';

export default function RoleForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;
  const [kode, setKode] = useState('');
  const [label, setLabel] = useState('');
  const [allPerms, setAllPerms] = useState([]);
  const [selectedPerms, setSelectedPerms] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEdit);

  useEffect(() => {
    getPermissions({ per_page: 100 }).then((res) => setAllPerms(res.data.data || []));
    if (isEdit) {
      getRole(id).then((res) => {
        setKode(res.data.data.kode); setLabel(res.data.data.label);
        setSelectedPerms(res.data.data.permissions || []);
      }).catch(() => toast.error('Gagal memuat data')).finally(() => setFetching(false));
    }
  }, [id, isEdit]);

  const togglePerm = (permKode) => {
    setSelectedPerms((prev) => prev.includes(permKode) ? prev.filter(p => p !== permKode) : [...prev, permKode]);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const payload = { kode, label, permissions: selectedPerms };
      if (isEdit) { await updateRole(id, payload); toast.success('Role berhasil diperbarui'); }
      else { await createRole(payload); toast.success('Role berhasil dibuat'); }
      navigate('/roles');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error('Terjadi kesalahan');
    }
    setLoading(false);
  };

  if (fetching) return <div className="flex justify-center py-16"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>;

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit Role' : 'Tambah Role'} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Roles', to: '/roles' }, { label: isEdit ? 'Edit' : 'Tambah' }]} />
      <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div className="max-w-lg grid grid-cols-2 gap-5 mb-6">
          <FormInput label="Kode Role" name="kode" value={kode} onChange={(e) => setKode(e.target.value)} error={errors.kode?.[0]} required placeholder="Contoh: admin_it" />
          <FormInput label="Label" name="label" value={label} onChange={(e) => setLabel(e.target.value)} error={errors.label?.[0]} required placeholder="Contoh: Admin IT" />
        </div>
        <div className="mb-6">
          <label className="block text-sm font-medium text-gray-700 mb-3">Permissions</label>
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            {allPerms.map((p) => (
              <label key={p.id} className={`flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition text-sm ${selectedPerms.includes(p.kode) ? 'border-wash-500 bg-wash-50 text-wash-800' : 'border-gray-200 hover:bg-gray-50'}`}>
                <input type="checkbox" checked={selectedPerms.includes(p.kode)} onChange={() => togglePerm(p.kode)} className="rounded border-gray-300 text-wash-600 focus:ring-wash-500" />
                <span className="truncate">{p.nama}</span>
              </label>
            ))}
          </div>
        </div>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

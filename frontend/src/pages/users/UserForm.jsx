import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getUser, createUser, updateUser } from '../../api/users';
import { getRoles } from '../../api/roles';
import PageHeader from '../../components/ui/PageHeader';
import FormInput from '../../components/ui/FormInput';
import FormSelect from '../../components/ui/FormSelect';
import FormGrid from '../../components/ui/FormGrid';
import FormActions from '../../components/ui/FormActions';

export default function UserForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;
  const [form, setForm] = useState({ nama: '', email: '', password: '', no_telp: '', role_id: '' });
  const [roles, setRoles] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEdit);

  useEffect(() => {
    getRoles({ per_page: 100 }).then((res) => setRoles(res.data.data.map(r => ({ value: r.id, label: `${r.label} (${r.kode})` }))));
    if (isEdit) {
      getUser(id).then((res) => {
        const u = res.data.data;
        setForm({ nama: u.nama || '', email: u.email || '', password: '', no_telp: u.no_telp || '', role_id: u.role?.id || '' });
      }).catch(() => toast.error('Gagal memuat data')).finally(() => setFetching(false));
    }
  }, [id, isEdit]);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const payload = { ...form };
      if (isEdit && !payload.password) delete payload.password;
      if (isEdit) { await updateUser(id, payload); toast.success('User berhasil diperbarui'); }
      else { await createUser(payload); toast.success('User berhasil dibuat'); }
      navigate('/users');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error('Terjadi kesalahan');
    }
    setLoading(false);
  };

  if (fetching) return <div className="flex justify-center py-16"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>;

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit User' : 'Tambah User'} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Users', to: '/users' }, { label: isEdit ? 'Edit' : 'Tambah' }]} />
      <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <FormGrid>
          <FormInput label="Nama Lengkap" name="nama" value={form.nama} onChange={handleChange} error={errors.nama?.[0]} required placeholder="Masukkan nama" />
          <FormInput label="Email" name="email" type="email" value={form.email} onChange={handleChange} error={errors.email?.[0]} required placeholder="Masukkan email" />
          <FormSelect label="Role" name="role_id" value={form.role_id} onChange={handleChange} options={roles} error={errors.role_id?.[0]} required placeholder="Pilih role" />
          <FormInput label={isEdit ? 'Password (kosongkan jika tidak diubah)' : 'Password'} name="password" type="password" value={form.password} onChange={handleChange} error={errors.password?.[0]} required={!isEdit} placeholder="Masukkan password" />
          <FormInput label="No. Telepon" name="no_telp" value={form.no_telp} onChange={handleChange} error={errors.no_telp?.[0]} placeholder="Masukkan no. telepon" />
        </FormGrid>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getOutlet, createOutlet, updateOutlet } from '../../../api/outlets';
import PageHeader from '../../../components/ui/PageHeader';
import FormInput from '../../../components/ui/FormInput';
import FormGrid from '../../../components/ui/FormGrid';
import FormActions from '../../../components/ui/FormActions';

export default function OutletForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;
  const [form, setForm] = useState({ nama: '', kode_outlet: '', alamat: '', franchise_id: '' });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEdit);

  useEffect(() => {
    if (isEdit) {
      getOutlet(id).then((res) => {
        const o = res.data.data;
        setForm({ nama: o.nama || '', kode_outlet: o.kode_outlet || '', alamat: o.alamat || '', franchise_id: o.franchise?.id || '' });
      }).catch(() => toast.error('Gagal memuat data')).finally(() => setFetching(false));
    }
  }, [id, isEdit]);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const payload = { ...form, franchise_id: parseInt(form.franchise_id) };
      if (isEdit) { await updateOutlet(id, payload); toast.success('Outlet berhasil diperbarui'); }
      else { await createOutlet(payload); toast.success('Outlet berhasil dibuat'); }
      navigate('/franchise/outlets');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error(err.response?.data?.message || 'Terjadi kesalahan');
    }
    setLoading(false);
  };

  if (fetching) return <div className="flex justify-center py-16"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>;

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit Outlet' : 'Tambah Outlet'} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Outlet', to: '/franchise/outlets' }, { label: isEdit ? 'Edit' : 'Tambah' }]} />
      <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <FormGrid>
          <FormInput label="Nama Outlet" name="nama" value={form.nama} onChange={handleChange} error={errors.nama?.[0]} required placeholder="Contoh: Outlet Jakarta Selatan" />
          <FormInput label="Kode Outlet" name="kode_outlet" value={form.kode_outlet} onChange={handleChange} error={errors.kode_outlet?.[0]} required placeholder="Contoh: OUT-JKT-001" disabled={isEdit} />
          <FormInput label="ID Franchise" name="franchise_id" type="number" value={form.franchise_id} onChange={handleChange} error={errors.franchise_id?.[0]} required placeholder="Masukkan ID franchise" />
          <FormInput label="Alamat" name="alamat" value={form.alamat} onChange={handleChange} error={errors.alamat?.[0]} required placeholder="Alamat lengkap outlet" />
        </FormGrid>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

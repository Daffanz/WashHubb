import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getService, createService, updateService } from '../../../api/services';
import PageHeader from '../../../components/ui/PageHeader';
import FormInput from '../../../components/ui/FormInput';
import FormGrid from '../../../components/ui/FormGrid';
import FormActions from '../../../components/ui/FormActions';

export default function ServiceForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;
  const [form, setForm] = useState({ nama: '', harga_standar_per_kg: '' });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEdit);

  useEffect(() => {
    if (isEdit) {
      getService(id).then((res) => {
        const s = res.data.data;
        setForm({ nama: s.nama || '', harga_standar_per_kg: s.harga_standar_per_kg || '' });
      }).catch(() => toast.error('Gagal memuat data')).finally(() => setFetching(false));
    }
  }, [id, isEdit]);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const payload = { ...form, harga_standar_per_kg: parseFloat(form.harga_standar_per_kg) || 0 };
      if (isEdit) { await updateService(id, payload); toast.success('Layanan berhasil diperbarui'); }
      else { await createService(payload); toast.success('Layanan berhasil dibuat'); }
      navigate('/master/services');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error('Terjadi kesalahan');
    }
    setLoading(false);
  };

  if (fetching) return <div className="flex justify-center py-16"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>;

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit Layanan' : 'Tambah Layanan'} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Layanan', to: '/master/services' }, { label: isEdit ? 'Edit' : 'Tambah' }]} />
      <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <FormGrid>
          <FormInput label="Nama Layanan" name="nama" value={form.nama} onChange={handleChange} error={errors.nama?.[0]} required placeholder="Contoh: Cuci Kering" />
          <FormInput label="Harga per Kg" name="harga_standar_per_kg" type="number" value={form.harga_standar_per_kg} onChange={handleChange} error={errors.harga_standar_per_kg?.[0]} required placeholder="Masukkan harga" />
        </FormGrid>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

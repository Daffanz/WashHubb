import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getMachine, createMachine, updateMachine } from '../../../api/machines';
import PageHeader from '../../../components/ui/PageHeader';
import FormInput from '../../../components/ui/FormInput';
import FormGrid from '../../../components/ui/FormGrid';
import FormActions from '../../../components/ui/FormActions';

export default function MachineForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;
  const [form, setForm] = useState({ nama: '', kode_mesin: '', merk: '', tipe: '', kapasitas: '', harga_standar: '' });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEdit);

  useEffect(() => {
    if (isEdit) {
      getMachine(id).then((res) => {
        const m = res.data.data;
        setForm({ nama: m.nama || '', kode_mesin: m.kode_mesin || '', merk: m.merk || '', tipe: m.tipe || '', kapasitas: m.kapasitas || '', harga_standar: m.harga_standar || '' });
      }).catch(() => toast.error('Gagal memuat data')).finally(() => setFetching(false));
    }
  }, [id, isEdit]);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const payload = { ...form, kapasitas: parseInt(form.kapasitas) || null, harga_standar: parseFloat(form.harga_standar) || 0 };
      if (isEdit) { await updateMachine(id, payload); toast.success('Mesin berhasil diperbarui'); }
      else { await createMachine(payload); toast.success('Mesin berhasil dibuat'); }
      navigate('/master/machines');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error('Terjadi kesalahan');
    }
    setLoading(false);
  };

  if (fetching) return <div className="flex justify-center py-16"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>;

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit Mesin' : 'Tambah Mesin'} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Mesin', to: '/master/machines' }, { label: isEdit ? 'Edit' : 'Tambah' }]} />
      <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <FormGrid>
          <FormInput label="Nama Mesin" name="nama" value={form.nama} onChange={handleChange} error={errors.nama?.[0]} required placeholder="Contoh: Mesin Cuci Front Load" />
          <FormInput label="Kode Mesin" name="kode_mesin" value={form.kode_mesin} onChange={handleChange} error={errors.kode_mesin?.[0]} required placeholder="Contoh: MC-001" disabled={isEdit} />
          <FormInput label="Merk" name="merk" value={form.merk} onChange={handleChange} error={errors.merk?.[0]} placeholder="Contoh: Samsung" />
          <FormInput label="Tipe" name="tipe" value={form.tipe} onChange={handleChange} error={errors.tipe?.[0]} placeholder="Contoh: Front Load" />
          <FormInput label="Kapasitas (kg)" name="kapasitas" type="number" value={form.kapasitas} onChange={handleChange} error={errors.kapasitas?.[0]} placeholder="Contoh: 10" />
          <FormInput label="Harga Standar" name="harga_standar" type="number" value={form.harga_standar} onChange={handleChange} error={errors.harga_standar?.[0]} required placeholder="Masukkan harga" />
        </FormGrid>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getMaterial, createMaterial, updateMaterial } from '../../../api/materials';
import { getCategories } from '../../../api/categories';
import PageHeader from '../../../components/ui/PageHeader';
import FormInput from '../../../components/ui/FormInput';
import FormSelect from '../../../components/ui/FormSelect';
import FormGrid from '../../../components/ui/FormGrid';
import FormActions from '../../../components/ui/FormActions';

export default function MaterialForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;
  const [form, setForm] = useState({ kategori_id: '', nama: '', satuan: '', harga_standar: '' });
  const [categories, setCategories] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEdit);

  useEffect(() => {
    getCategories({ per_page: 100 }).then((res) => setCategories(res.data.data.map((c) => ({ value: c.id, label: c.nama }))));
    if (isEdit) {
      getMaterial(id).then((res) => {
        const m = res.data.data;
        setForm({ kategori_id: m.kategori?.id || '', nama: m.nama || '', satuan: m.satuan || '', harga_standar: m.harga_standar || '' });
      }).catch(() => toast.error('Gagal memuat data')).finally(() => setFetching(false));
    }
  }, [id, isEdit]);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const payload = { ...form, harga_standar: parseFloat(form.harga_standar) || 0 };
      if (isEdit) { await updateMaterial(id, payload); toast.success('Bahan baku berhasil diperbarui'); }
      else { await createMaterial(payload); toast.success('Bahan baku berhasil dibuat'); }
      navigate('/master/materials');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error('Terjadi kesalahan');
    }
    setLoading(false);
  };

  if (fetching) return <div className="flex justify-center py-16"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>;

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit Bahan Baku' : 'Tambah Bahan Baku'} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Bahan Baku', to: '/master/materials' }, { label: isEdit ? 'Edit' : 'Tambah' }]} />
      <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <FormGrid>
          <FormSelect label="Kategori" name="kategori_id" value={form.kategori_id} onChange={handleChange} options={categories} error={errors.kategori_id?.[0]} required placeholder="Pilih kategori" />
          <FormInput label="Nama Bahan Baku" name="nama" value={form.nama} onChange={handleChange} error={errors.nama?.[0]} required placeholder="Contoh: Deterjen Bubuk" />
          <FormInput label="Satuan" name="satuan" value={form.satuan} onChange={handleChange} error={errors.satuan?.[0]} required placeholder="Contoh: kg, liter, pcs" />
          <FormInput label="Harga Standar" name="harga_standar" type="number" value={form.harga_standar} onChange={handleChange} error={errors.harga_standar?.[0]} required placeholder="Masukkan harga" />
        </FormGrid>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

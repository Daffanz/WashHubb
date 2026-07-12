import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getCategory, createCategory, updateCategory } from '../../../api/categories';
import PageHeader from '../../../components/ui/PageHeader';
import FormInput from '../../../components/ui/FormInput';
import FormActions from '../../../components/ui/FormActions';

export default function CategoryForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;
  const [nama, setNama] = useState('');
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEdit);

  useEffect(() => {
    if (isEdit) {
      getCategory(id).then((res) => setNama(res.data.data.nama)).catch(() => toast.error('Gagal memuat data')).finally(() => setFetching(false));
    }
  }, [id, isEdit]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      if (isEdit) { await updateCategory(id, { nama }); toast.success('Kategori berhasil diperbarui'); }
      else { await createCategory({ nama }); toast.success('Kategori berhasil dibuat'); }
      navigate('/master/categories');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error('Terjadi kesalahan');
    }
    setLoading(false);
  };

  if (fetching) return <div className="flex justify-center py-16"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>;

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit Kategori' : 'Tambah Kategori'} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Kategori', to: '/master/categories' }, { label: isEdit ? 'Edit' : 'Tambah' }]} />
      <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div className="max-w-lg">
          <FormInput label="Nama Kategori" name="nama" value={nama} onChange={(e) => setNama(e.target.value)} error={errors.nama?.[0]} required placeholder="Contoh: Deterjen" />
        </div>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

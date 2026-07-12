import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { createMutation } from '../../api/mutations';
import { getStocks } from '../../api/stocks';
import PageHeader from '../../components/ui/PageHeader';
import FormSelect from '../../components/ui/FormSelect';
import FormInput from '../../components/ui/FormInput';
import FormTextarea from '../../components/ui/FormTextarea';
import FormGrid from '../../components/ui/FormGrid';
import FormActions from '../../components/ui/FormActions';
import { JENIS_MUTASI_OPTIONS } from '../../utils/constants';

export default function MutationForm() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ stok_type: '', stok_id: '', jenis_mutasi: '', jumlah: '', keterangan: '' });
  const [stockOptions, setStockOptions] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  const stockTypeOptions = [
    { value: 'App\\Models\\StokPusatBahanBaku', label: 'Stok Bahan Baku' },
    { value: 'App\\Models\\StokPusatMesin', label: 'Stok Mesin' },
  ];

  useEffect(() => {
    if (form.stok_type) {
      const typeParam = form.stok_type.includes('BahanBaku') ? 'bahan' : 'mesin';
      getStocks({ per_page: 100, type: typeParam }).then((res) => {
        const items = res.data.data;
        if (typeParam === 'bahan') {
          setStockOptions(items.map((s) => ({ value: s.id, label: `${s.bahan_baku?.nama || `Stok ${s.id}`}` })));
        } else {
          setStockOptions(items.map((s) => ({ value: s.id, label: `${s.mesin?.nama || `Stok ${s.id}`}` })));
        }
      });
      setForm({ ...form, stok_id: '' });
    }
  }, [form.stok_type]);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const payload = { ...form, jumlah: parseFloat(form.jumlah) || 0 };
      await createMutation(payload);
      toast.success('Mutasi stok berhasil dicatat');
      navigate('/inventory/mutations');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error(err.response?.data?.message || 'Terjadi kesalahan');
    }
    setLoading(false);
  };

  return (
    <div>
      <PageHeader title="Buat Mutasi Stok" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Mutasi', to: '/inventory/mutations' }, { label: 'Buat' }]} />
      <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <FormGrid>
          <FormSelect label="Tipe Stok" name="stok_type" value={form.stok_type} onChange={handleChange} options={stockTypeOptions} error={errors.stok_type?.[0]} required placeholder="Pilih tipe stok" />
          <FormSelect label="Item Stok" name="stok_id" value={form.stok_id} onChange={handleChange} options={stockOptions} error={errors.stok_id?.[0]} required placeholder={form.stok_type ? 'Pilih item' : 'Pilih tipe stok dulu'} disabled={!form.stok_type} />
          <FormSelect label="Jenis Mutasi" name="jenis_mutasi" value={form.jenis_mutasi} onChange={handleChange} options={JENIS_MUTASI_OPTIONS} error={errors.jenis_mutasi?.[0]} required placeholder="Pilih jenis" />
          <FormInput label="Jumlah" name="jumlah" type="number" value={form.jumlah} onChange={handleChange} error={errors.jumlah?.[0]} required placeholder="Masukkan jumlah" />
          <div className="md:col-span-2">
            <FormTextarea label="Keterangan" name="keterangan" value={form.keterangan} onChange={handleChange} placeholder="Keterangan opsional" rows={2} />
          </div>
        </FormGrid>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

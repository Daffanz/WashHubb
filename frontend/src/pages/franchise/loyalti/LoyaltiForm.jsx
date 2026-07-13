import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { createLoyalti } from '../../../api/loyalti';
import { getOutlets } from '../../../api/outlets';
import PageHeader from '../../../components/ui/PageHeader';
import FormSelect from '../../../components/ui/FormSelect';
import FormInput from '../../../components/ui/FormInput';
import FormGrid from '../../../components/ui/FormGrid';
import FormActions from '../../../components/ui/FormActions';

export default function LoyaltiForm() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ outlet_id: '', periode: '', target_omset: '', target_operasional: '' });
  const [outlets, setOutlets] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    getOutlets({ per_page: 100 }).then((res) => setOutlets(res.data.data.map((o) => ({ value: o.id, label: o.nama }))));
  }, []);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      await createLoyalti({
        outlet_id: parseInt(form.outlet_id),
        periode: form.periode,
        target_omset: parseFloat(form.target_omset),
        target_operasional: parseFloat(form.target_operasional),
      });
      toast.success('Target loyalti berhasil dibuat');
      navigate('/franchise/loyalti');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error(err.response?.data?.message || 'Terjadi kesalahan');
    }
    setLoading(false);
  };

  return (
    <div>
      <PageHeader title="Buat Target Loyalti" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Loyalti', to: '/franchise/loyalti' }, { label: 'Buat' }]} />
      <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <FormGrid>
          <FormSelect label="Outlet" name="outlet_id" value={form.outlet_id} onChange={handleChange} options={outlets} error={errors.outlet_id?.[0]} required placeholder="Pilih outlet" />
          <FormInput label="Periode" name="periode" value={form.periode} onChange={handleChange} error={errors.periode?.[0]} required placeholder="Contoh: 2026-07" />
          <FormInput label="Target Omset (Rp)" name="target_omset" type="number" value={form.target_omset} onChange={handleChange} error={errors.target_omset?.[0]} required placeholder="Contoh: 50000000" />
          <FormInput label="Target Operasional (%)" name="target_operasional" type="number" step="0.01" value={form.target_operasional} onChange={handleChange} error={errors.target_operasional?.[0]} required placeholder="Contoh: 85" />
        </FormGrid>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

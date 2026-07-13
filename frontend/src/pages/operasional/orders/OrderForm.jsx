import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { createOrder } from '../../../api/orderCucian';
import { getOutlets } from '../../../api/outlets';
import { getServices } from '../../../api/services';
import { getMachines } from '../../../api/machines';
import PageHeader from '../../../components/ui/PageHeader';
import FormInput from '../../../components/ui/FormInput';
import FormSelect from '../../../components/ui/FormSelect';
import FormGrid from '../../../components/ui/FormGrid';
import FormActions from '../../../components/ui/FormActions';

export default function OrderForm() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ outlet_id: '', jenis_layanan_id: '', detail_mesin_id: '', berat: '' });
  const [outlets, setOutlets] = useState([]);
  const [layanan, setLayanan] = useState([]);
  const [mesins, setMesins] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    getOutlets({ per_page: 100 }).then((res) => setOutlets(res.data.data.map((o) => ({ value: o.id, label: o.nama }))));
    getServices({ per_page: 100 }).then((res) => setLayanan(res.data.data.map((s) => ({ value: s.id, label: s.nama }))));
    getMachines({ per_page: 100 }).then((res) => setMesins(res.data.data.filter((m) => m.status?.kode === 'aktif').map((m) => ({ value: m.id, label: `${m.nama} (${m.kode_mesin})` }))));
  }, []);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      await createOrder({ ...form, berat: parseFloat(form.berat) });
      toast.success('Order cucian berhasil dibuat');
      navigate('/operasional/orders');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error(err.response?.data?.message || 'Terjadi kesalahan');
    }
    setLoading(false);
  };

  return (
    <div>
      <PageHeader title="Buat Order Cucian" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Orders', to: '/operasional/orders' }, { label: 'Buat' }]} />
      <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <FormGrid>
          <FormSelect label="Outlet" name="outlet_id" value={form.outlet_id} onChange={handleChange} options={outlets} error={errors.outlet_id?.[0]} required placeholder="Pilih outlet" />
          <FormSelect label="Jenis Layanan" name="jenis_layanan_id" value={form.jenis_layanan_id} onChange={handleChange} options={layanan} error={errors.jenis_layanan_id?.[0]} required placeholder="Pilih layanan" />
          <FormSelect label="Mesin" name="detail_mesin_id" value={form.detail_mesin_id} onChange={handleChange} options={mesins} error={errors.detail_mesin_id?.[0]} required placeholder="Pilih mesin (aktif)" />
          <FormInput label="Berat (kg)" name="berat" type="number" step="0.01" value={form.berat} onChange={handleChange} error={errors.berat?.[0]} required placeholder="Contoh: 5.5" />
        </FormGrid>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

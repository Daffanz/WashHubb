import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { createJadwalService } from '../../../api/jadwalService';
import { getOutlets } from '../../../api/outlets';
import { getMachines } from '../../../api/machines';
import PageHeader from '../../../components/ui/PageHeader';
import FormSelect from '../../../components/ui/FormSelect';
import FormInput from '../../../components/ui/FormInput';
import FormTextarea from '../../../components/ui/FormTextarea';
import FormGrid from '../../../components/ui/FormGrid';
import FormActions from '../../../components/ui/FormActions';

export default function ServiceForm() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ detail_mesin_id: '', outlet_id: '', deskripsi: '' });
  const [outlets, setOutlets] = useState([]);
  const [mesins, setMesins] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    getOutlets({ per_page: 100 }).then((res) => setOutlets(res.data.data.map((o) => ({ value: o.id, label: o.nama }))));
    getMachines({ per_page: 100 }).then((res) => setMesins(res.data.data.map((m) => ({ value: m.id, label: `${m.nama} (${m.kode_mesin})` }))));
  }, []);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      await createJadwalService(form);
      toast.success('Pengajuan service berhasil');
      navigate('/operasional/jadwal-service');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error(err.response?.data?.message || 'Terjadi kesalahan');
    }
    setLoading(false);
  };

  return (
    <div>
      <PageHeader title="Ajukan Service Mesin" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Service', to: '/operasional/jadwal-service' }, { label: 'Ajukan' }]} />
      <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <FormGrid>
          <FormSelect label="Outlet" name="outlet_id" value={form.outlet_id} onChange={handleChange} options={outlets} error={errors.outlet_id?.[0]} required placeholder="Pilih outlet" />
          <FormSelect label="Mesin" name="detail_mesin_id" value={form.detail_mesin_id} onChange={handleChange} options={mesins} error={errors.detail_mesin_id?.[0]} required placeholder="Pilih mesin" />
          <div className="md:col-span-2">
            <FormTextarea label="Deskripsi Kerusakan" name="deskripsi" value={form.deskripsi} onChange={handleChange} error={errors.deskripsi?.[0]} required placeholder="Jelaskan kerusakan atau kebutuhan service..." />
          </div>
        </FormGrid>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getOutlet, createOutlet, updateOutlet, getFranchises, getManajerOperasionals } from '../../../api/outlets';
import PageHeader from '../../../components/ui/PageHeader';
import FormInput from '../../../components/ui/FormInput';
import FormGrid from '../../../components/ui/FormGrid';
import FormActions from '../../../components/ui/FormActions';

export default function OutletForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;
  const [form, setForm] = useState({ nama: '', kode_outlet: '', alamat: '', franchise_id: '', manager_outlet_id: '' });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEdit);
  const [franchises, setFranchises] = useState([]);
  const [franchiseSearch, setFranchiseSearch] = useState('');
  const [showFranchiseDropdown, setShowFranchiseDropdown] = useState(false);
  const [managers, setManagers] = useState([]);

  useEffect(() => {
    // Load franchises
    getFranchises().then((res) => setFranchises(res.data.data || [])).catch(() => {});
    // Load manajer operasionals
    getManajerOperasionals().then((res) => setManagers(res.data.data || [])).catch(() => {});

    if (isEdit) {
      getOutlet(id).then((res) => {
        const o = res.data.data;
        setForm({
          nama: o.nama || '',
          kode_outlet: o.kode_outlet || '',
          alamat: o.alamat || '',
          franchise_id: o.franchise?.id || '',
          manager_outlet_id: o.manager_outlet?.id || '',
        });
        if (o.franchise?.user?.nama) {
          setFranchiseSearch(o.franchise.user.nama);
        }
      }).catch(() => toast.error('Gagal memuat data')).finally(() => setFetching(false));
    }
  }, [id, isEdit]);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const filteredFranchises = franchises.filter(f =>
    f.user?.nama?.toLowerCase().includes(franchiseSearch.toLowerCase()) ||
    f.user?.email?.toLowerCase().includes(franchiseSearch.toLowerCase())
  );

  const selectFranchise = (franchise) => {
    setForm({ ...form, franchise_id: franchise.id });
    setFranchiseSearch(franchise.user?.nama || '');
    setShowFranchiseDropdown(false);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const payload = {
        ...form,
        franchise_id: parseInt(form.franchise_id),
        manager_outlet_id: form.manager_outlet_id ? parseInt(form.manager_outlet_id) : null,
      };
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

          {/* Franchise Dropdown with Search */}
          <div className="relative">
            <label className="block text-sm font-medium text-gray-700 mb-1.5">Franchise <span className="text-red-500">*</span></label>
            <input
              type="text"
              value={franchiseSearch}
              onChange={(e) => { setFranchiseSearch(e.target.value); setShowFranchiseDropdown(true); }}
              onFocus={() => setShowFranchiseDropdown(true)}
              className="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-wash-500 focus:border-wash-500 transition"
              placeholder="Cari franchise..."
            />
            {showFranchiseDropdown && filteredFranchises.length > 0 && (
              <div className="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                {filteredFranchises.map((f) => (
                  <button
                    key={f.id}
                    type="button"
                    onClick={() => selectFranchise(f)}
                    className="w-full text-left px-4 py-3 hover:bg-gray-50 transition border-b border-gray-100 last:border-0"
                  >
                    <p className="text-sm font-medium text-gray-900">{f.user?.nama || '-'}</p>
                    <p className="text-xs text-gray-500">{f.user?.email || '-'}</p>
                  </button>
                ))}
              </div>
            )}
            {errors.franchise_id && <p className="mt-1 text-xs text-red-500">{errors.franchise_id[0]}</p>}
          </div>

          {/* Manager Outlet Dropdown */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1.5">Manager Outlet</label>
            <select
              name="manager_outlet_id"
              value={form.manager_outlet_id}
              onChange={handleChange}
              className="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-wash-500 focus:border-wash-500 transition"
            >
              <option value="">Pilih Manager Outlet (opsional)</option>
              {managers.map((m) => (
                <option key={m.id} value={m.user_id}>{m.user?.nama} ({m.user?.email})</option>
              ))}
            </select>
            {errors.manager_outlet_id && <p className="mt-1 text-xs text-red-500">{errors.manager_outlet_id[0]}</p>}
          </div>

          <FormInput label="Alamat" name="alamat" value={form.alamat} onChange={handleChange} error={errors.alamat?.[0]} required placeholder="Alamat lengkap outlet" />
        </FormGrid>
        <FormActions loading={loading} />
      </form>
    </div>
  );
}

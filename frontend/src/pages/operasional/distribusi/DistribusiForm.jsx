import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { createDistribusi } from '../../../api/distribusiOutlet';
import { getPermintaans } from '../../../api/permintaanStok';
import { getMaterials } from '../../../api/materials';
import PageHeader from '../../../components/ui/PageHeader';
import FormSelect from '../../../components/ui/FormSelect';
import FormInput from '../../../components/ui/FormInput';
import FormActions from '../../../components/ui/FormActions';

export default function DistribusiForm() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ permintaan_stok_outlet_id: '', tanggal_kirim: '' });
  const [permintaans, setPermintaans] = useState([]);
  const [materials, setMaterials] = useState([]);
  const [items, setItems] = useState([{ bahan_baku_id: '', jumlah_kirim: '' }]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    getPermintaans({ per_page: 100 }).then((res) => setPermintaans(res.data.data.filter((p) => ['disetujui', 'disetujui_sebagian'].includes(p.status?.kode)).map((p) => ({ value: p.id, label: `#${p.id} - ${p.outlet?.nama || '-'}` }))));
    getMaterials({ per_page: 100 }).then((res) => setMaterials(res.data.data.map((m) => ({ value: m.id, label: `${m.nama} (${m.satuan})` }))));
  }, []);

  const addItem = () => setItems([...items, { bahan_baku_id: '', jumlah_kirim: '' }]);
  const removeItem = (i) => setItems(items.filter((_, idx) => idx !== i));
  const updateItem = (i, field, val) => { const n = [...items]; n[i][field] = val; setItems(n); };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const validItems = items.filter((i) => i.bahan_baku_id && i.jumlah_kirim);
      if (validItems.length === 0) { toast.error('Tambah minimal 1 item'); setLoading(false); return; }
      await createDistribusi({
        permintaan_stok_outlet_id: parseInt(form.permintaan_stok_outlet_id),
        tanggal_kirim: form.tanggal_kirim,
        items: validItems.map((i) => ({ bahan_baku_id: parseInt(i.bahan_baku_id), jumlah_kirim: parseFloat(i.jumlah_kirim) })),
      });
      toast.success('Distribusi berhasil dibuat');
      navigate('/operasional/distribusi-outlet');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error(err.response?.data?.message || 'Terjadi kesalahan');
    }
    setLoading(false);
  };

  return (
    <div>
      <PageHeader title="Buat Distribusi Outlet" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Distribusi', to: '/operasional/distribusi-outlet' }, { label: 'Buat' }]} />
      <form onSubmit={handleSubmit}>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            <FormSelect label="Permintaan Stok" name="permintaan_stok_outlet_id" value={form.permintaan_stok_outlet_id} onChange={(e) => setForm({ ...form, permintaan_stok_outlet_id: e.target.value })} options={permintaans} error={errors.permintaan_stok_outlet_id?.[0]} required placeholder="Pilih permintaan" />
            <FormInput label="Tanggal Kirim" name="tanggal_kirim" type="date" value={form.tanggal_kirim} onChange={(e) => setForm({ ...form, tanggal_kirim: e.target.value })} error={errors.tanggal_kirim?.[0]} required />
          </div>
        </div>

        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-sm font-semibold text-gray-900">Items</h3>
            <button type="button" onClick={addItem} className="text-sm text-wash-700 hover:text-wash-900 font-medium">+ Tambah Item</button>
          </div>
          <div className="space-y-3">
            {items.map((item, i) => (
              <div key={i} className="grid grid-cols-1 md:grid-cols-3 gap-3 items-end p-3 bg-gray-50 rounded-lg">
                <FormSelect label={i === 0 ? 'Bahan Baku' : ''} value={item.bahan_baku_id} onChange={(e) => updateItem(i, 'bahan_baku_id', e.target.value)} options={materials} placeholder="Pilih bahan" />
                <FormInput label={i === 0 ? 'Jumlah Kirim' : ''} type="number" step="any" value={item.jumlah_kirim} onChange={(e) => updateItem(i, 'jumlah_kirim', e.target.value)} placeholder="Jumlah kirim" />
                <div className={i === 0 ? 'pt-5' : ''}>{items.length > 1 && <button type="button" onClick={() => removeItem(i)} className="text-sm text-red-600 hover:text-red-800">Hapus</button>}</div>
              </div>
            ))}
          </div>
        </div>

        <FormActions loading={loading} />
      </form>
    </div>
  );
}

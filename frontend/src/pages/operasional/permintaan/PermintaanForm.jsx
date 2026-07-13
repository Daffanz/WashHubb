import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { createPermintaan } from '../../../api/permintaanStok';
import { getOutlets } from '../../../api/outlets';
import { getMaterials } from '../../../api/materials';
import PageHeader from '../../../components/ui/PageHeader';
import FormSelect from '../../../components/ui/FormSelect';
import FormInput from '../../../components/ui/FormInput';
import FormActions from '../../../components/ui/FormActions';

export default function PermintaanForm() {
  const navigate = useNavigate();
  const [outletId, setOutletId] = useState('');
  const [outlets, setOutlets] = useState([]);
  const [materials, setMaterials] = useState([]);
  const [items, setItems] = useState([{ bahan_baku_id: '', jumlah_diminta: '' }]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    getOutlets({ per_page: 100 }).then((res) => setOutlets(res.data.data.map((o) => ({ value: o.id, label: o.nama }))));
    getMaterials({ per_page: 100 }).then((res) => setMaterials(res.data.data.map((m) => ({ value: m.id, label: `${m.nama} (${m.satuan})` }))));
  }, []);

  const addItem = () => setItems([...items, { bahan_baku_id: '', jumlah_diminta: '' }]);
  const removeItem = (i) => setItems(items.filter((_, idx) => idx !== i));
  const updateItem = (i, field, val) => { const n = [...items]; n[i][field] = val; setItems(n); };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const validItems = items.filter((i) => i.bahan_baku_id && i.jumlah_diminta);
      if (validItems.length === 0) { toast.error('Tambah minimal 1 item'); setLoading(false); return; }
      await createPermintaan({ outlet_id: parseInt(outletId), items: validItems.map((i) => ({ bahan_baku_id: parseInt(i.bahan_baku_id), jumlah_diminta: parseFloat(i.jumlah_diminta) })) });
      toast.success('Permintaan berhasil diajukan');
      navigate('/operasional/permintaan-stok');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error(err.response?.data?.message || 'Terjadi kesalahan');
    }
    setLoading(false);
  };

  return (
    <div>
      <PageHeader title="Buat Permintaan Stok" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Permintaan Stok', to: '/operasional/permintaan-stok' }, { label: 'Buat' }]} />
      <form onSubmit={handleSubmit}>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
          <FormSelect label="Outlet" name="outlet_id" value={outletId} onChange={(e) => setOutletId(e.target.value)} options={outlets} error={errors.outlet_id?.[0]} required placeholder="Pilih outlet" />
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
                <FormInput label={i === 0 ? 'Jumlah' : ''} type="number" step="any" value={item.jumlah_diminta} onChange={(e) => updateItem(i, 'jumlah_diminta', e.target.value)} placeholder="Jumlah diminta" />
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

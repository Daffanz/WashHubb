import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { createPermintaan } from '../../../api/permintaanStok';
import { getOutlets } from '../../../api/outlets';
import { getMaterials } from '../../../api/materials';
import { getMachines } from '../../../api/machines';
import PageHeader from '../../../components/ui/PageHeader';
import FormSelect from '../../../components/ui/FormSelect';
import FormInput from '../../../components/ui/FormInput';
import FormActions from '../../../components/ui/FormActions';

export default function PermintaanForm() {
  const navigate = useNavigate();
  const [outletId, setOutletId] = useState('');
  const [outlets, setOutlets] = useState([]);
  const [materials, setMaterials] = useState([]);
  const [machines, setMachines] = useState([]);
  const [items, setItems] = useState([{ tipe_item: 'bahan_baku', bahan_baku_id: '', mesin_id: '', jumlah_diminta: '' }]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    getOutlets({ per_page: 100 }).then((res) => setOutlets(res.data.data.map((o) => ({ value: o.id, label: o.nama }))));
    getMaterials({ per_page: 100 }).then((res) => setMaterials(res.data.data.map((m) => ({ value: m.id, label: `${m.nama} (${m.satuan})` }))));
    getMachines({ per_page: 100 }).then((res) => setMachines(res.data.data.map((m) => ({ value: m.id, label: `${m.nama} (${m.merk})` }))));
  }, []);

  const addItem = () => setItems([...items, { tipe_item: 'bahan_baku', bahan_baku_id: '', mesin_id: '', jumlah_diminta: '' }]);
  const removeItem = (i) => setItems(items.filter((_, idx) => idx !== i));
  const updateItem = (i, field, val) => { const n = [...items]; n[i][field] = val; setItems(n); };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const validItems = items.filter((i) => {
        if (i.tipe_item === 'bahan_baku') return i.bahan_baku_id && i.jumlah_diminta;
        return i.mesin_id && i.jumlah_diminta;
      });
      if (validItems.length === 0) { toast.error('Tambah minimal 1 item'); setLoading(false); return; }
      await createPermintaan({
        outlet_id: parseInt(outletId),
        items: validItems.map((i) => ({
          tipe_item: i.tipe_item,
          bahan_baku_id: i.tipe_item === 'bahan_baku' ? parseInt(i.bahan_baku_id) : null,
          mesin_id: i.tipe_item === 'mesin' ? parseInt(i.mesin_id) : null,
          jumlah_diminta: parseFloat(i.jumlah_diminta),
        })),
      });
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
              <div key={i} className="grid grid-cols-1 md:grid-cols-4 gap-3 items-end p-3 bg-gray-50 rounded-lg">
                <div>
                  {i === 0 && <label className="block text-sm font-medium text-gray-700 mb-1.5">Tipe Item</label>}
                  <select
                    value={item.tipe_item}
                    onChange={(e) => updateItem(i, 'tipe_item', e.target.value)}
                    className="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-wash-500 focus:border-wash-500 transition"
                  >
                    <option value="bahan_baku">Bahan Baku</option>
                    <option value="mesin">Mesin</option>
                  </select>
                </div>
                {item.tipe_item === 'bahan_baku' ? (
                  <FormSelect label={i === 0 ? 'Bahan Baku' : ''} value={item.bahan_baku_id} onChange={(e) => updateItem(i, 'bahan_baku_id', e.target.value)} options={materials} placeholder="Pilih bahan" />
                ) : (
                  <FormSelect label={i === 0 ? 'Mesin' : ''} value={item.mesin_id} onChange={(e) => updateItem(i, 'mesin_id', e.target.value)} options={machines} placeholder="Pilih mesin" />
                )}
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

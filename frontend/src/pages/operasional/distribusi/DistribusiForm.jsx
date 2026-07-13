import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { createDistribusi } from '../../../api/distribusiOutlet';
import { getPermintaans, getPermintaan } from '../../../api/permintaanStok';
import PageHeader from '../../../components/ui/PageHeader';
import FormSelect from '../../../components/ui/FormSelect';
import FormInput from '../../../components/ui/FormInput';
import FormActions from '../../../components/ui/FormActions';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';

export default function DistribusiForm() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ permintaan_stok_outlet_id: '', tanggal_kirim: '' });
  const [permintaans, setPermintaans] = useState([]);
  const [items, setItems] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetchingItems, setFetchingItems] = useState(false);

  useEffect(() => {
    getPermintaans({ per_page: 100 }).then((res) => setPermintaans(res.data.data.filter((p) => ['disetujui', 'disetujui_sebagian'].includes(p.status?.kode)).map((p) => ({ value: p.id, label: `#${p.id} - ${p.outlet?.nama || '-'}` }))));
  }, []);

  // Fetch permintaan details when selected
  useEffect(() => {
    if (!form.permintaan_stok_outlet_id) {
      setItems([]);
      return;
    }

    setFetchingItems(true);
    getPermintaan(form.permintaan_stok_outlet_id)
      .then((res) => {
        const permintaan = res.data.data;
        const autoItems = permintaan.details
          ?.filter((d) => d.status?.kode === 'disetujui' || d.status?.kode === 'disetujui_sebagian')
          .map((d) => ({
            tipe_item: d.tipe_item || 'bahan_baku',
            bahan_baku_id: d.bahan_baku?.id || '',
            mesin_id: d.mesin?.id || '',
            nama_item: d.tipe_item === 'mesin' ? d.mesin?.nama : d.bahan_baku?.nama,
            jumlah_diminta: d.jumlah_diminta,
            jumlah_disetujui: d.jumlah_disetujui,
            jumlah_kirim: d.jumlah_disetujui || d.jumlah_diminta || '',
          })) || [];
        setItems(autoItems);
      })
      .catch(() => toast.error('Gagal memuat detail permintaan'))
      .finally(() => setFetchingItems(false));
  }, [form.permintaan_stok_outlet_id]);

  const updateItem = (i, field, val) => { const n = [...items]; n[i][field] = val; setItems(n); };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const validItems = items.filter((i) => i.jumlah_kirim && parseFloat(i.jumlah_kirim) > 0);
      if (validItems.length === 0) { toast.error('Tidak ada item yang bisa dikirim'); setLoading(false); return; }
      await createDistribusi({
        permintaan_stok_outlet_id: parseInt(form.permintaan_stok_outlet_id),
        tanggal_kirim: form.tanggal_kirim,
        items: validItems.map((i) => ({
          tipe_item: i.tipe_item,
          bahan_baku_id: i.tipe_item === 'bahan_baku' && i.bahan_baku_id ? parseInt(i.bahan_baku_id) : null,
          mesin_id: i.tipe_item === 'mesin' && i.mesin_id ? parseInt(i.mesin_id) : null,
          jumlah_kirim: parseFloat(i.jumlah_kirim),
        })),
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
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Items (otomatis dari permintaan)</h3>
          {fetchingItems ? (
            <LoadingSpinner />
          ) : items.length === 0 ? (
            <p className="text-sm text-gray-500">Pilih permintaan stok terlebih dahulu</p>
          ) : (
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm">
                <thead><tr className="border-b">
                  <th className="text-left py-2 text-gray-500">Tipe</th>
                  <th className="text-left py-2 text-gray-500">Item</th>
                  <th className="text-right py-2 text-gray-500">Diminta</th>
                  <th className="text-right py-2 text-gray-500">Disetujui</th>
                  <th className="text-right py-2 text-gray-500">Jumlah Kirim</th>
                </tr></thead>
                <tbody>
                  {items.map((item, i) => (
                    <tr key={i} className="border-b last:border-0">
                      <td className="py-2">
                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${item.tipe_item === 'mesin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}`}>
                          {item.tipe_item === 'mesin' ? 'Mesin' : 'Bahan Baku'}
                        </span>
                      </td>
                      <td className="py-2 font-medium">{item.nama_item || '-'}</td>
                      <td className="py-2 text-right">{item.jumlah_diminta}</td>
                      <td className="py-2 text-right">{item.jumlah_disetujui ?? '-'}</td>
                      <td className="py-2 text-right">
                        <input
                          type="number"
                          step="any"
                          min="0"
                          max={item.jumlah_disetujui || item.jumlah_diminta}
                          value={item.jumlah_kirim}
                          onChange={(e) => updateItem(i, 'jumlah_kirim', e.target.value)}
                          className="w-24 px-2 py-1 text-sm border border-gray-300 rounded text-right"
                        />
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

        <FormActions loading={loading} />
      </form>
    </div>
  );
}

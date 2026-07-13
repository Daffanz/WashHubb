import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { createMutation } from '../../api/mutations';
import { getStocks } from '../../api/stocks';
import PageHeader from '../../components/ui/PageHeader';
import FormSelect from '../../components/ui/FormSelect';
import FormInput from '../../components/ui/FormInput';
import FormActions from '../../components/ui/FormActions';
import { JENIS_MUTASI_OPTIONS } from '../../utils/constants';
import { formatQty } from '../../utils/format';

export default function MutationForm() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ stok_type: '', stok_id: '', jenis_mutasi: '', referensi_penerimaan_id: '', jumlah: '' });
  const [stockOptions, setStockOptions] = useState([]);
  const [penerimaanOptions, setPenerimaanOptions] = useState([]);
  const [selectedPenerimaan, setSelectedPenerimaan] = useState(null);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  const stockTypeOptions = [
    { value: 'bahan', label: 'Stok Bahan Baku' },
    { value: 'mesin', label: 'Stok Mesin' },
  ];

  // Load stok options untuk keluar
  useEffect(() => {
    if (form.stok_type && form.jenis_mutasi === 'keluar') {
      getStocks({ per_page: 100, type: form.stok_type }).then((res) => {
        const items = res.data.data;
        setStockOptions(items.map((s) => ({
          value: s.id,
          label: form.stok_type === 'bahan' ? s.bahan_baku?.nama : s.mesin?.nama,
        })));
      });
      setForm(prev => ({ ...prev, stok_id: '' }));
    }
  }, [form.stok_type, form.jenis_mutasi]);

  // Load penerimaan selesai untuk masuk
  useEffect(() => {
    if (form.jenis_mutasi === 'masuk') {
      const token = localStorage.getItem('washhub_token');
      fetch('/api/procurement/receipts?per_page=100', { headers: { Authorization: `Bearer ${token}` } })
        .then(r => r.json()).then(d => {
          // Hanya receipt selesai yang bisa dimutasi
          setPenerimaanOptions((d.data || [])
            .filter(p => p.status?.kode === 'selesai')
            .map(p => ({ value: p.id, label: `${p.nomor_penerimaan} - PO ${p.distribusi_barang?.po?.nomor_po || ''}` })));
        });
    } else {
      setPenerimaanOptions([]);
      setSelectedPenerimaan(null);
      setForm(prev => ({ ...prev, referensi_penerimaan_id: '' }));
    }
  }, [form.jenis_mutasi]);

  // Load detail penerimaan
  useEffect(() => {
    if (form.referensi_penerimaan_id && form.jenis_mutasi === 'masuk') {
      const token = localStorage.getItem('washhub_token');
      fetch(`/api/procurement/receipts/${form.referensi_penerimaan_id}`, { headers: { Authorization: `Bearer ${token}` } })
        .then(r => r.json()).then(d => setSelectedPenerimaan(d.data));
    }
  }, [form.referensi_penerimaan_id]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm(prev => ({ ...prev, [name]: value }));
    if (name === 'stok_type') {
      setForm(prev => ({ ...prev, stok_type: value, stok_id: '', referensi_penerimaan_id: '' }));
      setSelectedPenerimaan(null); setStockOptions([]); setPenerimaanOptions([]);
    }
    if (name === 'jenis_mutasi') {
      setForm(prev => ({ ...prev, jenis_mutasi: value, stok_id: '', referensi_penerimaan_id: '', jumlah: '' }));
      setSelectedPenerimaan(null);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      if (form.jenis_mutasi === 'masuk') {
        await createMutation({
          stok_type: form.stok_type,
          jenis_mutasi: 'masuk',
          referensi_penerimaan_id: parseInt(form.referensi_penerimaan_id),
        });
      } else {
        await createMutation({
          stok_type: form.stok_type,
          stok_id: parseInt(form.stok_id),
          jenis_mutasi: 'keluar',
          jumlah: parseFloat(form.jumlah) || 0,
        });
      }
      toast.success('Mutasi stok berhasil dicatat');
      navigate('/inventory/mutations');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error(err.response?.data?.message || 'Terjadi kesalahan');
    }
    setLoading(false);
  };

  const isMasuk = form.jenis_mutasi === 'masuk';

  return (
    <div>
      <PageHeader title="Buat Mutasi Barang" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Mutasi', to: '/inventory/mutations' }, { label: 'Buat' }]} />
      <form onSubmit={handleSubmit}>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            <FormSelect label="Tipe Stok" name="stok_type" value={form.stok_type} onChange={handleChange} options={stockTypeOptions} error={errors.stok_type?.[0]} required placeholder="Pilih tipe stok" />
            <FormSelect label="Jenis Mutasi" name="jenis_mutasi" value={form.jenis_mutasi} onChange={handleChange} options={JENIS_MUTASI_OPTIONS} error={errors.jenis_mutasi?.[0]} required placeholder="Pilih jenis" disabled={!form.stok_type} />
          </div>
        </div>

        {/* MASUK: Pilih Penerimaan */}
        {isMasuk && form.stok_type && (
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <h3 className="text-sm font-semibold text-gray-900 mb-4">Referensi Penerimaan</h3>
            <FormSelect label="Pilih Penerimaan" name="referensi_penerimaan_id" value={form.referensi_penerimaan_id} onChange={handleChange} options={penerimaanOptions} error={errors.referensi_penerimaan_id?.[0]} required placeholder={penerimaanOptions.length ? 'Pilih penerimaan' : 'Tidak ada penerimaan tersedia'} />
            {penerimaanOptions.length === 0 && (
              <p className="text-xs text-amber-600 bg-amber-50 px-3 py-2 rounded-lg mt-2">
                Tidak ada penerimaan selesai. Pastikan semua retur sudah selesai terlebih dahulu.
              </p>
            )}
            {selectedPenerimaan && (
              <div className="mt-4">
                <h4 className="text-xs font-medium text-gray-500 mb-2">Detail Items (yang kondisi baik):</h4>
                <div className="overflow-x-auto">
                  <table className="min-w-full text-sm">
                    <thead><tr className="border-b">
                      <th className="text-left py-2 text-gray-500">Item</th>
                      <th className="text-right py-2 text-gray-500">Diterima</th>
                      <th className="text-center py-2 text-gray-500">Kondisi</th>
                      <th className="text-right py-2 text-emerald-600 font-semibold">Stok Masuk</th>
                    </tr></thead>
                    <tbody>
                      {[...(selectedPenerimaan.items_bahan_baku || []), ...(selectedPenerimaan.items_mesin || [])].map((item, i) => (
                        <tr key={i} className={`border-b last:border-0 ${item.kondisi === 'cacat' ? 'bg-red-50' : ''}`}>
                          <td className="py-2 font-medium">{item.nama || `Item #${i + 1}`}</td>
                          <td className="py-2 text-right">{formatQty(item.qty_diterima)}</td>
                          <td className="py-2 text-center"><span className={`text-xs font-medium px-2 py-0.5 rounded ${item.kondisi === 'baik' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}`}>{item.kondisi}</span></td>
                          <td className="py-2 text-right font-bold text-emerald-600">{item.kondisi === 'baik' ? `+${formatQty(item.qty_diterima)}` : '0'}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}
          </div>
        )}

        {/* KELUAR */}
        {!isMasuk && form.stok_type && (
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              <FormSelect label="Item Stok" name="stok_id" value={form.stok_id} onChange={handleChange} options={stockOptions} error={errors.stok_id?.[0]} required placeholder="Pilih item" />
              <FormInput label="Jumlah" name="jumlah" type="number" value={form.jumlah} onChange={handleChange} error={errors.jumlah?.[0]} required placeholder="Masukkan jumlah" />
            </div>
          </div>
        )}

        <FormActions loading={loading} />
      </form>
    </div>
  );
}

import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import FormInput from '../../components/ui/FormInput';
import FormGrid from '../../components/ui/FormGrid';
import FormActions from '../../components/ui/FormActions';
import { formatQty, formatRupiah } from '../../utils/format';

export default function ReceiptForm() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ distribusi_barang_id: '', tanggal_terima: '', subtotal: '', diskon: '0', ppn: '0', total_bayar: '' });
  const [distributions, setDistributions] = useState([]);
  const [selectedDist, setSelectedDist] = useState(null);
  const [itemsBB, setItemsBB] = useState([]);
  const [itemsMesin, setItemsMesin] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const token = localStorage.getItem('washhub_token');
    fetch('/api/procurement/distributions?per_page=100', { headers: { Authorization: `Bearer ${token}` } })
      .then(r => r.json()).then(d => {
        setDistributions((d.data || []).filter(dist => dist.status?.kode === 'dikirim' || dist.status?.kode === 'dikirim_sebagian').map(dist => ({ value: dist.id, label: `${dist.nomor_distribusi} - PO ${dist.po?.nomor_po || ''}` })));
      });
  }, []);

  const handleDistSelect = (distId) => {
    setForm(prev => ({ ...prev, distribusi_barang_id: distId }));
    const token = localStorage.getItem('washhub_token');
    fetch(`/api/procurement/distributions/${distId}`, { headers: { Authorization: `Bearer ${token}` } })
      .then(r => r.json()).then(d => {
        const dist = d.data;
        setSelectedDist(dist);
        setItemsBB((dist.items_bahan_baku || []).map(i => ({ distribusi_detail_id: i.id, nama: i.nama, jumlah_kirim: i.jumlah_kirim, harga_satuan: i.harga_satuan || 0, qty_diterima: i.jumlah_kirim, kondisi: 'baik' })));
        setItemsMesin((dist.items_mesin || []).map(i => ({ distribusi_detail_id: i.id, nama: i.nama, jumlah_kirim: i.jumlah_kirim, harga_satuan: i.harga_satuan || 0, qty_diterima: i.jumlah_kirim, kondisi: 'baik' })));

        // Auto-calculate subtotal dari harga_satuan × jumlah_kirim
        const allItems = [...(dist.items_bahan_baku || []), ...(dist.items_mesin || [])];
        const autoSubtotal = allItems.reduce((sum, i) => sum + ((i.harga_satuan || 0) * (i.jumlah_kirim || 0)), 0);
        setForm(prev => ({ ...prev, subtotal: autoSubtotal.toString(), total_bayar: autoSubtotal.toString() }));
      });
  };

  // Auto-calculate subtotal dari items (harga_satuan × jumlah_kirim)
  const calcSubtotal = () => {
    return [...itemsBB, ...itemsMesin].reduce((sum, i) => sum + ((i.harga_satuan || 0) * (i.jumlah_kirim || 0)), 0);
  };

  const calcTotal = () => {
    const subtotal = calcSubtotal();
    const diskon = parseFloat(form.diskon) || 0;
    const ppn = parseFloat(form.ppn) || 0;
    return subtotal - diskon + ppn;
  };

  const hasCacat = [...itemsBB, ...itemsMesin].some(i => i.kondisi === 'cacat');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    const payload = {
      distribusi_barang_id: parseInt(form.distribusi_barang_id),
      tanggal_terima: form.tanggal_terima,
      subtotal: calcSubtotal(),
      diskon: parseFloat(form.diskon) || 0,
      ppn: parseFloat(form.ppn) || 0,
      total_bayar: calcTotal(),
    };
    if (itemsBB.length) payload.items_bahan_baku = itemsBB.map(i => ({ distribusi_detail_id: i.distribusi_detail_id, qty_diterima: parseFloat(i.qty_diterima), kondisi: i.kondisi }));
    if (itemsMesin.length) payload.items_mesin = itemsMesin.map(i => ({ distribusi_detail_id: i.distribusi_detail_id, qty_diterima: parseInt(i.qty_diterima), kondisi: i.kondisi, nomor_seri: i.nomor_seri || null }));
    try {
      const res = await fetch('/api/procurement/receipts', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${localStorage.getItem('washhub_token')}` }, body: JSON.stringify(payload) });
      if (!res.ok) { const d = await res.json(); setErrors(d.errors || {}); throw new Error(d.message); }
      toast.success('Penerimaan berhasil dicatat');
      navigate('/procurement/receipts');
    } catch (err) { toast.error(err.message || 'Terjadi kesalahan'); }
    setLoading(false);
  };

  return (
    <div>
      <PageHeader title="Catat Penerimaan" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Penerimaan', to: '/procurement/receipts' }, { label: 'Buat' }]} />
      <form onSubmit={handleSubmit}>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Penerimaan</h3>
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-1.5">Distribusi *</label>
            <select value={form.distribusi_barang_id} onChange={(e) => handleDistSelect(e.target.value)} required className="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-wash-500">
              <option value="">Pilih distribusi</option>
              {distributions.map(opt => <option key={opt.value} value={opt.value}>{opt.label}</option>)}
            </select>
          </div>
          <FormGrid>
            <FormInput label="Tanggal Terima" name="tanggal_terima" type="date" value={form.tanggal_terima} onChange={(e) => setForm(prev => ({ ...prev, tanggal_terima: e.target.value }))} required />
            <div></div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Subtotal (otomatis)</label>
              <div className="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-medium">
                {new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(calcSubtotal())}
              </div>
            </div>
            <FormInput label="Diskon" name="diskon" type="number" value={form.diskon} onChange={(e) => setForm(prev => ({ ...prev, diskon: e.target.value }))} placeholder="0" />
            <FormInput label="PPN" name="ppn" type="number" value={form.ppn} onChange={(e) => setForm(prev => ({ ...prev, ppn: e.target.value }))} placeholder="0" />
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Total Bayar</label>
              <div className="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-bold">
                {new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(calcTotal())}
              </div>
            </div>
          </FormGrid>
        </div>

        {selectedDist && (
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <h3 className="text-sm font-semibold text-gray-900 mb-4">Detail Penerimaan</h3>
            {hasCacat}
            {itemsBB.length > 0 && (
              <div className="mb-4">
                <h4 className="text-xs font-medium text-gray-500 mb-2">Bahan Baku</h4>
                {itemsBB.map((item, i) => (
                  <div key={i} className={`flex items-center gap-3 mb-2 p-3 rounded-lg ${item.kondisi === 'cacat' ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50'}`}>
                    <span className="flex-1 text-sm font-medium">{item.nama}</span>
                    <span className="text-xs text-gray-500">Dikirim: {formatQty(item.jumlah_kirim)}</span>
                    <div className="flex items-center gap-2">
                      <label className="text-xs text-gray-500">Diterima:</label>
                      <input type="number" value={item.qty_diterima} onChange={(e) => { const n = [...itemsBB]; n[i].qty_diterima = e.target.value; setItemsBB(n); }} className="w-20 px-2 py-1 text-sm border border-gray-300 rounded-lg" step="any" />
                    </div>
                    <div className="flex items-center gap-2">
                      <label className="text-xs text-gray-500">Kondisi:</label>
                      <select value={item.kondisi} onChange={(e) => { const n = [...itemsBB]; n[i].kondisi = e.target.value; setItemsBB(n); }} className="px-2 py-1 text-sm border border-gray-300 rounded-lg">
                        <option value="baik">Baik</option>
                        <option value="cacat">Cacat</option>
                      </select>
                    </div>
                  </div>
                ))}
              </div>
            )}
            {itemsMesin.length > 0 && (
              <div>
                <h4 className="text-xs font-medium text-gray-500 mb-2">Mesin</h4>
                {itemsMesin.map((item, i) => (
                  <div key={i} className={`flex items-center gap-3 mb-2 p-3 rounded-lg flex-wrap ${item.kondisi === 'cacat' ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50'}`}>
                    <span className="flex-1 text-sm font-medium">{item.nama}</span>
                    <span className="text-xs text-gray-500">Dikirim: {formatQty(item.jumlah_kirim)}</span>
                    <div className="flex items-center gap-2">
                      <label className="text-xs text-gray-500">Diterima:</label>
                      <input type="number" value={item.qty_diterima} onChange={(e) => { const n = [...itemsMesin]; n[i].qty_diterima = e.target.value; setItemsMesin(n); }} className="w-20 px-2 py-1 text-sm border border-gray-300 rounded-lg" />
                    </div>
                    <div className="flex items-center gap-2">
                      <label className="text-xs text-gray-500">Nomor Seri:</label>
                      <input type="text" value={item.nomor_seri || ''} onChange={(e) => { const n = [...itemsMesin]; n[i].nomor_seri = e.target.value; setItemsMesin(n); }} className="w-32 px-2 py-1 text-sm border border-gray-300 rounded-lg" placeholder="—" />
                    </div>
                    <div className="flex items-center gap-2">
                      <label className="text-xs text-gray-500">Kondisi:</label>
                      <select value={item.kondisi} onChange={(e) => { const n = [...itemsMesin]; n[i].kondisi = e.target.value; setItemsMesin(n); }} className="px-2 py-1 text-sm border border-gray-300 rounded-lg">
                        <option value="baik">Baik</option>
                        <option value="cacat">Cacat</option>
                      </select>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        <FormActions loading={loading} />
      </form>
    </div>
  );
}

import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import FormInput from '../../components/ui/FormInput';
import FormTextarea from '../../components/ui/FormTextarea';
import FormActions from '../../components/ui/FormActions';
import { formatQty } from '../../utils/format';

export default function DistributionForm() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ po_id: '', tanggal_kirim: '' });
  const [poOptions, setPoOptions] = useState([]);
  const [selectedPO, setSelectedPO] = useState(null);
  const [itemsBB, setItemsBB] = useState([]);
  const [itemsMesin, setItemsMesin] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const token = localStorage.getItem('washhub_token');
    // UC-35: Supplier lihat PO yang sudah disetujui
    fetch('/api/procurement/purchase-orders?per_page=100', { headers: { Authorization: `Bearer ${token}` } })
      .then(r => r.json()).then(d => {
        setPoOptions((d.data || []).filter(po => po.status?.kode === 'disetujui' || po.status?.kode === 'disetujui_sebagian').map(po => ({ value: po.id, label: `${po.nomor_po} - ${po.supplier?.nama || ''} (${po.status?.label})` })));
      });
  }, []);

  const handlePOSelect = (poId) => {
    setForm(prev => ({ ...prev, po_id: poId }));
    const token = localStorage.getItem('washhub_token');
    fetch(`/api/procurement/purchase-orders/${poId}`, { headers: { Authorization: `Bearer ${token}` } })
      .then(r => r.json()).then(d => {
        const po = d.data;
        setSelectedPO(po);
        // Hanya item yang disetujui
        setItemsBB((po.items_bahan_baku || []).filter(i => i.status === 'disetujui' || i.status === 'disetujui_sebagian').map(i => ({ po_item_id: i.id, nama: i.nama, qty_disetujui: i.qty_disetujui, jumlah_kirim: i.qty_disetujui })));
        setItemsMesin((po.items_mesin || []).filter(i => i.status === 'disetujui' || i.status === 'disetujui_sebagian').map(i => ({ po_item_id: i.id, nama: i.nama, qty_disetujui: i.qty_disetujui, jumlah_kirim: i.qty_disetujui })));
      });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    const payload = { po_id: parseInt(form.po_id), tanggal_kirim: form.tanggal_kirim };
    if (itemsBB.length) payload.items_bahan_baku = itemsBB.map(i => ({ po_item_id: i.po_item_id, jumlah_kirim: parseFloat(i.jumlah_kirim) }));
    if (itemsMesin.length) payload.items_mesin = itemsMesin.map(i => ({ po_item_id: i.po_item_id, jumlah_kirim: parseInt(i.jumlah_kirim) }));
    try {
      const res = await fetch('/api/procurement/distributions', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${localStorage.getItem('washhub_token')}` }, body: JSON.stringify(payload) });
      if (!res.ok) { const d = await res.json(); setErrors(d.errors || {}); throw new Error(d.message); }
      toast.success('Distribusi berhasil dibuat');
      navigate('/procurement/distributions');
    } catch (err) { toast.error(err.message || 'Terjadi kesalahan'); }
    setLoading(false);
  };

  return (
    <div>
      <PageHeader title="Buat Distribusi" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Distribusi', to: '/procurement/distributions' }, { label: 'Buat' }]} />
      <form onSubmit={handleSubmit}>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Distribusi</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Purchase Order (Disetujui) *</label>
              <select value={form.po_id} onChange={(e) => handlePOSelect(e.target.value)} required className="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-wash-500">
                <option value="">Pilih PO</option>
                {poOptions.map(opt => <option key={opt.value} value={opt.value}>{opt.label}</option>)}
              </select>
              {errors.po_id && <p className="mt-1 text-xs text-red-600">{errors.po_id[0]}</p>}
            </div>
            <FormInput label="Tanggal Kirim" name="tanggal_kirim" type="date" value={form.tanggal_kirim} onChange={(e) => setForm(prev => ({ ...prev, tanggal_kirim: e.target.value }))} error={errors.tanggal_kirim?.[0]} required />
          </div>
        </div>

        {selectedPO && (
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <h3 className="text-sm font-semibold text-gray-900 mb-4">Items yang Disetujui</h3>
            {itemsBB.length > 0 && (
              <div className="mb-4">
                <h4 className="text-xs font-medium text-gray-500 mb-2">Bahan Baku</h4>
                {itemsBB.map((item, i) => (
                  <div key={i} className="flex items-center gap-3 mb-2 p-3 bg-gray-50 rounded-lg">
                    <span className="flex-1 text-sm font-medium">{item.nama}</span>
                    <span className="text-xs text-gray-500">Disetujui: {formatQty(item.qty_disetujui)}</span>
                    <div className="flex items-center gap-2">
                      <label className="text-xs text-gray-500">Kirim:</label>
                      <input type="number" value={item.jumlah_kirim} onChange={(e) => { const n = [...itemsBB]; n[i].jumlah_kirim = e.target.value; setItemsBB(n); }} className="w-24 px-2 py-1 text-sm border border-gray-300 rounded-lg" step="any" min="0" max={item.qty_disetujui} />
                    </div>
                  </div>
                ))}
              </div>
            )}
            {itemsMesin.length > 0 && (
              <div>
                <h4 className="text-xs font-medium text-gray-500 mb-2">Mesin</h4>
                {itemsMesin.map((item, i) => (
                  <div key={i} className="flex items-center gap-3 mb-2 p-3 bg-gray-50 rounded-lg">
                    <span className="flex-1 text-sm font-medium">{item.nama}</span>
                    <span className="text-xs text-gray-500">Disetujui: {item.qty_disetujui}</span>
                    <div className="flex items-center gap-2">
                      <label className="text-xs text-gray-500">Kirim:</label>
                      <input type="number" value={item.jumlah_kirim} onChange={(e) => { const n = [...itemsMesin]; n[i].jumlah_kirim = e.target.value; setItemsMesin(n); }} className="w-24 px-2 py-1 text-sm border border-gray-300 rounded-lg" min="0" max={item.qty_disetujui} />
                    </div>
                  </div>
                ))}
              </div>
            )}
            {itemsBB.length === 0 && itemsMesin.length === 0 && <p className="text-sm text-gray-400">Tidak ada item yang disetujui</p>}
          </div>
        )}

        <FormActions loading={loading} />
      </form>
    </div>
  );
}

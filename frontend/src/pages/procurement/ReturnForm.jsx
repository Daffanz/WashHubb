import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import FormActions from '../../components/ui/FormActions';
import { formatQty } from '../../utils/format';

export default function ReturnForm() {
  const navigate = useNavigate();
  const [penerimaans, setPenerimaans] = useState([]);
  const [selectedPenerimaan, setSelectedPenerimaan] = useState(null);
  const [returItemsBB, setReturItemsBB] = useState([]);
  const [returItemsMesin, setReturItemsMesin] = useState([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    const token = localStorage.getItem('washhub_token');
    fetch('/api/procurement/receipts?per_page=100', { headers: { Authorization: `Bearer ${token}` } })
      .then(r => r.json()).then(d => setPenerimaans(d.data || []))
      .catch(() => toast.error('Gagal memuat penerimaan'))
      .finally(() => setLoading(false));
  }, []);

  const selectPenerimaan = (p) => {
    setSelectedPenerimaan(p);
    // Tampilkan semua item — user pilih mana yang mau diretur
    setReturItemsBB((p.items_bahan_baku || []).map(i => ({
      penerimaan_detail_id: i.id, qty_diterima: i.qty_diterima, kondisi: i.kondisi,
      qty_retur: 0, alasan: '',
    })));
    setReturItemsMesin((p.items_mesin || []).map(i => ({
      penerimaan_detail_id: i.id, qty_diterima: i.qty_diterima, kondisi: i.kondisi,
      nomor_seri: i.nomor_seri,
      qty_retur: 0, alasan: '',
    })));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!selectedPenerimaan) { toast.error('Pilih penerimaan'); return; }

    const returBB = returItemsBB.filter(i => i.qty_retur > 0);
    const returMesin = returItemsMesin.filter(i => i.qty_retur > 0);
    if (returBB.length === 0 && returMesin.length === 0) {
      toast.error('Minimal ada 1 item dengan qty retur > 0'); return;
    }

    setSubmitting(true);
    try {
      const token = localStorage.getItem('washhub_token');
      const payload = { penerimaan_barang_id: selectedPenerimaan.id };
      if (returBB.length) {
        payload.items_bahan_baku = returBB.map(i => ({
          penerimaan_detail_id: i.penerimaan_detail_id,
          qty_retur: parseFloat(i.qty_retur),
          alasan: i.alasan || 'Barang cacat/kurang',
        }));
      }
      if (returMesin.length) {
        payload.items_mesin = returMesin.map(i => ({
          penerimaan_detail_id: i.penerimaan_detail_id,
          qty_retur: parseInt(i.qty_retur),
          alasan: i.alasan || 'Barang cacat/kurang',
        }));
      }
      const res = await fetch('/api/procurement/returns', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${token}` }, body: JSON.stringify(payload) });
      if (!res.ok) { const d = await res.json(); throw new Error(d.message); }
      toast.success('Retur berhasil dibuat');
      navigate('/procurement/returns');
    } catch (err) { toast.error(err.message || 'Gagal'); }
    setSubmitting(false);
  };

  if (loading) return <LoadingSpinner />;

  return (
    <div>
      <PageHeader title="Buat Retur" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Retur', to: '/procurement/returns' }, { label: 'Buat' }]} />

      {!selectedPenerimaan ? (
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-3">Pilih Penerimaan</h3>
          <p className="text-xs text-gray-500 mb-3">Pilih penerimaan barang yang ingin diretur.</p>
          {penerimaans.length === 0 ? (
            <p className="text-sm text-gray-400">Belum ada penerimaan.</p>
          ) : (
            <div className="space-y-2">
              {penerimaans.map(p => (
                <button key={p.id} onClick={() => selectPenerimaan(p)} className="w-full text-left p-4 bg-gray-50 rounded-lg border border-gray-100 hover:border-wash-300 hover:bg-wash-50 transition">
                  <div className="flex justify-between items-center">
                    <div>
                      <span className="font-mono font-medium text-wash-800">{p.nomor_penerimaan}</span>
                      <span className="text-sm text-gray-500 ml-2">PO: {p.distribusi_barang?.po?.nomor_po || '-'}</span>
                    </div>
                    <span className="text-xs text-gray-400">{p.items_bahan_baku?.length || 0} + {p.items_mesin?.length || 0} item</span>
                  </div>
                </button>
              ))}
            </div>
          )}
        </div>
      ) : (
        <form onSubmit={handleSubmit}>
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <div className="flex justify-between items-start mb-4">
              <div>
                <h3 className="text-sm font-semibold text-gray-900">Penerimaan: {selectedPenerimaan.nomor_penerimaan}</h3>
                <p className="text-xs text-gray-500">PO: {selectedPenerimaan.distribusi_barang?.po?.nomor_po || '-'}</p>
              </div>
              <button type="button" onClick={() => { setSelectedPenerimaan(null); setReturItemsBB([]); setReturItemsMesin([]); }} className="text-xs text-blue-600 hover:text-blue-800 font-medium">Ganti</button>
            </div>

            <p className="text-xs text-gray-500 mb-4">Isi qty retur untuk item yang cacat/kehilangan. Qty 0 = tidak diretur.</p>

            {returItemsBB.length > 0 && (
              <div className="mb-4">
                <h4 className="text-xs font-medium text-gray-500 mb-2">Bahan Baku</h4>
                {returItemsBB.map((item, i) => (
                  <div key={i} className={`p-3 rounded-lg mb-2 ${item.qty_retur > 0 ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50'}`}>
                    <div className="flex items-center gap-3 mb-2">
                      <span className="flex-1 text-sm font-medium">BB #{i + 1}</span>
                      <span className="text-xs text-gray-500">Diterima: {formatQty(item.qty_diterima)}</span>
                      <span className={`text-xs font-medium px-2 py-0.5 rounded ${item.kondisi === 'baik' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}`}>{item.kondisi}</span>
                    </div>
                    <div className="flex items-center gap-3">
                      <div className="flex items-center gap-2">
                        <label className="text-xs text-gray-500">Retur:</label>
                        <input type="number" value={item.qty_retur} onChange={(e) => { const n = [...returItemsBB]; n[i].qty_retur = e.target.value; setReturItemsBB(n); }} className="w-20 px-2 py-1 text-sm border border-gray-300 rounded-lg" min="0" max={item.qty_diterima} step="any" />
                      </div>
                      <input type="text" value={item.alasan} onChange={(e) => { const n = [...returItemsBB]; n[i].alasan = e.target.value; setReturItemsBB(n); }} className="flex-1 px-2 py-1 text-sm border border-gray-300 rounded-lg" placeholder="Alasan retur..." />
                    </div>
                  </div>
                ))}
              </div>
            )}

            {returItemsMesin.length > 0 && (
              <div className="mb-4">
                <h4 className="text-xs font-medium text-gray-500 mb-2">Mesin</h4>
                {returItemsMesin.map((item, i) => (
                  <div key={i} className={`p-3 rounded-lg mb-2 ${item.qty_retur > 0 ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50'}`}>
                    <div className="flex items-center gap-3 mb-2">
                      <span className="flex-1 text-sm font-medium">Mesin #{i + 1}</span>
                      <span className="text-xs text-gray-500">Diterima: {item.qty_diterima}</span>
                      {item.nomor_seri && <span className="text-xs text-gray-400">SN: {item.nomor_seri}</span>}
                      <span className={`text-xs font-medium px-2 py-0.5 rounded ${item.kondisi === 'baik' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}`}>{item.kondisi}</span>
                    </div>
                    <div className="flex items-center gap-3">
                      <div className="flex items-center gap-2">
                        <label className="text-xs text-gray-500">Retur:</label>
                        <input type="number" value={item.qty_retur} onChange={(e) => { const n = [...returItemsMesin]; n[i].qty_retur = e.target.value; setReturItemsMesin(n); }} className="w-20 px-2 py-1 text-sm border border-gray-300 rounded-lg" min="0" max={item.qty_diterima} />
                      </div>
                      <input type="text" value={item.alasan} onChange={(e) => { const n = [...returItemsMesin]; n[i].alasan = e.target.value; setReturItemsMesin(n); }} className="flex-1 px-2 py-1 text-sm border border-gray-300 rounded-lg" placeholder="Alasan retur..." />
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          <FormActions loading={submitting} cancelTo="/procurement/returns" />
        </form>
      )}
    </div>
  );
}

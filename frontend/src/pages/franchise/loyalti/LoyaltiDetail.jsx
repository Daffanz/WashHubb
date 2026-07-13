import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getLoyalti, evaluateLoyalti, setBonusLoyalti, cairkanLoyalti, confirmPencairan } from '../../../api/loyalti';
import PageHeader from '../../../components/ui/PageHeader';
import StatusBadge from '../../../components/ui/StatusBadge';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import { formatRupiah } from '../../../utils/format';

export default function LoyaltiDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [loyalti, setLoyalti] = useState(null);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState(false);
  const [capaian, setCapaian] = useState('');
  const [bonus, setBonus] = useState('');
  const [keterangan, setKeterangan] = useState('');
  const [bukti, setBukti] = useState('');

  const fetchData = useCallback(async () => {
    try { const res = await getLoyalti(id); setLoyalti(res.data.data); }
    catch { toast.error('Gagal memuat data'); navigate(-1); }
    finally { setLoading(false); }
  }, [id, navigate]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const handleEvaluate = async () => {
    if (!capaian) { toast.error('Capaian operasional wajib diisi'); return; }
    setProcessing(true);
    try { await evaluateLoyalti(id, { capaian_operasional: parseFloat(capaian) }); toast.success('Evaluasi selesai'); await fetchData(); setCapaian(''); }
    catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setProcessing(false);
  };

  const handleSetBonus = async () => {
    if (!bonus) { toast.error('Jumlah bonus wajib diisi'); return; }
    setProcessing(true);
    try { await setBonusLoyalti(id, { jumlah_bonus: parseFloat(bonus), keterangan }); toast.success('Bonus ditetapkan'); await fetchData(); setBonus(''); setKeterangan(''); }
    catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setProcessing(false);
  };

  const handleCairkan = async () => {
    if (!bukti) { toast.error('Bukti transfer wajib diisi'); return; }
    setProcessing(true);
    try { await cairkanLoyalti(id, { bukti_transfer: bukti }); toast.success('Pencairan diproses'); await fetchData(); setBukti(''); }
    catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setProcessing(false);
  };

  const handleConfirm = async (status) => {
    setProcessing(true);
    try { await confirmPencairan(id, { status }); toast.success('Konfirmasi berhasil'); await fetchData(); }
    catch (err) { toast.error(err.response?.data?.message || 'Gagal'); }
    setProcessing(false);
  };

  if (loading) return <LoadingSpinner />;
  if (!loyalti) return null;

  const s = loyalti.status?.kode;

  return (
    <div>
      <PageHeader title={`Loyalti #${loyalti.id}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Loyalti', to: '/franchise/loyalti' }, { label: 'Detail' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Info Loyalti</h3>
          <dl className="space-y-3 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Outlet</dt><dd className="font-medium">{loyalti.outlet?.nama || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Periode</dt><dd>{loyalti.periode}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Target Omset</dt><dd>{formatRupiah(loyalti.target_omset)}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Target Operasional</dt><dd>{loyalti.target_operasional}%</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Omset Aktual</dt><dd className={loyalti.omset_aktual >= loyalti.target_omset ? 'text-emerald-600 font-semibold' : 'text-red-600 font-semibold'}>{formatRupiah(loyalti.omset_aktual)}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Capaian Operasional</dt><dd>{loyalti.capaian_operasional !== null ? `${loyalti.capaian_operasional}%` : '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Memenuhi Target</dt><dd>{loyalti.memenuhi_target ? <span className="text-emerald-600 font-medium">Ya</span> : <span className="text-red-600 font-medium">Tidak</span>}</dd></div>
            {loyalti.jumlah_bonus !== null && <div className="flex justify-between"><dt className="text-gray-500">Jumlah Bonus</dt><dd className="font-semibold text-wash-800">{formatRupiah(loyalti.jumlah_bonus)}</dd></div>}
            {loyalti.keterangan && <div className="flex justify-between"><dt className="text-gray-500">Keterangan</dt><dd className="text-right max-w-xs">{loyalti.keterangan}</dd></div>}
            <div className="flex justify-between pt-2 border-t"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={loyalti.status?.label} color={s === 'selesai' ? 'green' : s === 'tidak_memenuhi_target' || s === 'gagal' ? 'red' : s === 'menunggu_evaluasi' || s === 'menunggu_pencairan' ? 'yellow' : 'blue'} /></dd></div>
          </dl>
        </div>

        <div className="space-y-6">
          {/* Pencairan history */}
          {loyalti.pencairans?.length > 0 && (
            <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
              <h3 className="text-sm font-semibold text-gray-900 mb-4">Riwayat Pencairan</h3>
              <div className="space-y-2">
                {loyalti.pencairans.map((p) => (
                  <div key={p.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg text-sm">
                    <span>#{p.id} — {p.bukti_transfer || '-'}</span>
                    <StatusBadge status={p.status?.label} color={p.status?.kode === 'selesai' ? 'green' : p.status?.kode === 'gagal' ? 'red' : 'blue'} />
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Franchisor: Evaluasi */}
          {s === 'menunggu_evaluasi' && (
            <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
              <h3 className="text-sm font-semibold text-gray-900 mb-4">Evaluasi (Franchisor)</h3>
              <input type="number" step="0.01" value={capaian} onChange={(e) => setCapaian(e.target.value)} placeholder="Capaian operasional (%)" className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg mb-3" />
              <button onClick={handleEvaluate} disabled={processing} className="w-full px-4 py-2 text-sm font-medium text-white bg-wash-700 rounded-lg hover:bg-wash-800 disabled:opacity-50">
                {processing ? 'Memproses...' : 'Evaluasi'}
              </button>
            </div>
          )}

          {/* Franchisor: Set Bonus */}
          {s === 'memenuhi_target' && (
            <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
              <h3 className="text-sm font-semibold text-gray-900 mb-4">Tetapkan Bonus (Franchisor)</h3>
              <input type="number" value={bonus} onChange={(e) => setBonus(e.target.value)} placeholder="Jumlah bonus (Rp)" className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg mb-2" />
              <input type="text" value={keterangan} onChange={(e) => setKeterangan(e.target.value)} placeholder="Keterangan (opsional)" className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg mb-3" />
              <button onClick={handleSetBonus} disabled={processing} className="w-full px-4 py-2 text-sm font-medium text-white bg-wash-700 rounded-lg hover:bg-wash-800 disabled:opacity-50">
                {processing ? 'Memproses...' : 'Tetapkan Bonus'}
              </button>
            </div>
          )}

          {/* Franchisor: Cairkan */}
          {s === 'menunggu_pencairan' && (
            <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
              <h3 className="text-sm font-semibold text-gray-900 mb-4">Cairkan Bonus (Franchisor)</h3>
              <input type="text" value={bukti} onChange={(e) => setBukti(e.target.value)} placeholder="URL bukti transfer" className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg mb-3" />
              <button onClick={handleCairkan} disabled={processing} className="w-full px-4 py-2 text-sm font-medium text-white bg-wash-700 rounded-lg hover:bg-wash-800 disabled:opacity-50">
                {processing ? 'Memproses...' : 'Cairkan'}
              </button>
            </div>
          )}

          {/* Franchisee: Konfirmasi */}
          {s === 'diproses_pencairan' && (
            <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
              <h3 className="text-sm font-semibold text-gray-900 mb-4">Konfirmasi Penerimaan (Franchisee)</h3>
              <div className="flex gap-3">
                <button onClick={() => handleConfirm('diterima')} disabled={processing} className="flex-1 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50">
                  Diterima
                </button>
                <button onClick={() => handleConfirm('belum_diterima')} disabled={processing} className="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                  Belum Diterima
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

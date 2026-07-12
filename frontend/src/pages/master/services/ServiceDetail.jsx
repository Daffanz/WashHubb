import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getService, attachMaterial, detachMaterial } from '../../../api/services';
import { getMaterials } from '../../../api/materials';
import PageHeader from '../../../components/ui/PageHeader';
import FormSelect from '../../../components/ui/FormSelect';
import FormInput from '../../../components/ui/FormInput';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import ConfirmModal from '../../../components/ui/ConfirmModal';
import { formatRupiah } from '../../../utils/format';

export default function ServiceDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [service, setService] = useState(null);
  const [materials, setMaterials] = useState([]);
  const [allMaterials, setAllMaterials] = useState([]);
  const [form, setForm] = useState({ bahan_baku_id: '', jumlah_konsumsi: '' });
  const [loading, setLoading] = useState(true);
  const [attachLoading, setAttachLoading] = useState(false);
  const [detachTarget, setDetachTarget] = useState(null);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [sRes, mRes] = await Promise.all([getService(id), getMaterials({ per_page: 100 })]);
      setService(sRes.data.data);
      setMaterials(sRes.data.data.materials || []);
      setAllMaterials(mRes.data.data.map((m) => ({ value: m.id, label: `${m.nama} (${m.satuan})` })));
    } catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  };

  useEffect(() => { fetchData(); }, [id]);

  const handleAttach = async (e) => {
    e.preventDefault();
    setAttachLoading(true);
    try {
      await attachMaterial(id, { ...form, jumlah_konsumsi: parseFloat(form.jumlah_konsumsi) || 0 });
      toast.success('Material berhasil ditambahkan');
      setForm({ bahan_baku_id: '', jumlah_konsumsi: '' });
      fetchData();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Gagal menambahkan material');
    }
    setAttachLoading(false);
  };

  const handleDetach = async () => {
    try {
      await detachMaterial(id, detachTarget.id);
      toast.success('Material berhasil dihapus');
      setDetachTarget(null);
      fetchData();
    } catch { toast.error('Gagal menghapus material'); }
  };

  if (loading) return <LoadingSpinner />;

  return (
    <div>
      <PageHeader title={`Detail: ${service?.nama || ''}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Layanan', to: '/master/services' }, { label: 'Detail' }]} />

      {/* Info card */}
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <div className="grid grid-cols-2 gap-4 text-sm">
          <div><span className="text-gray-500">Nama:</span> <span className="font-medium ml-2">{service?.nama}</span></div>
          <div><span className="text-gray-500">Harga/kg:</span> <span className="font-medium ml-2">{formatRupiah(service?.harga_standar_per_kg)}</span></div>
        </div>
      </div>

      {/* Attach material form */}
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <h3 className="text-sm font-semibold text-gray-900 mb-4">Tambah Material</h3>
        <form onSubmit={handleAttach} className="flex flex-col sm:flex-row gap-4 items-end">
          <div className="flex-1 w-full">
            <FormSelect label="Material" name="bahan_baku_id" value={form.bahan_baku_id} onChange={(e) => setForm({ ...form, bahan_baku_id: e.target.value })} options={allMaterials} required placeholder="Pilih material" />
          </div>
          <div className="w-full sm:w-40">
            <FormInput label="Jumlah Konsumsi" name="jumlah_konsumsi" type="number" value={form.jumlah_konsumsi} onChange={(e) => setForm({ ...form, jumlah_konsumsi: e.target.value })} required placeholder="0.00" />
          </div>
          <button type="submit" disabled={attachLoading} className="px-4 py-2.5 bg-wash-900 text-white text-sm font-medium rounded-lg hover:bg-wash-800 transition disabled:opacity-50 whitespace-nowrap">
            {attachLoading ? 'Menambahkan...' : 'Tambah'}
          </button>
        </form>
      </div>

      {/* Materials table */}
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama Material</th>
              <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Satuan</th>
              <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Harga Standar</th>
              <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jumlah Konsumsi</th>
              <th className="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {materials.length === 0 ? (
              <tr><td colSpan={5} className="px-4 py-8 text-center text-sm text-gray-400">Belum ada material</td></tr>
            ) : materials.map((m) => (
              <tr key={m.id} className="hover:bg-gray-50/50">
                <td className="px-4 py-3 text-sm font-medium">{m.nama}</td>
                <td className="px-4 py-3 text-sm text-gray-600">{m.satuan}</td>
                <td className="px-4 py-3 text-sm text-gray-600">{formatRupiah(m.harga_standar)}</td>
                <td className="px-4 py-3 text-sm text-gray-600">{m.pivot?.jumlah_konsumsi}</td>
                <td className="px-4 py-3 text-right">
                  <button onClick={() => setDetachTarget(m)} className="text-sm text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50 transition">Hapus</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <ConfirmModal open={!!detachTarget} title="Hapus Material" message={`Yakin ingin menghapus "${detachTarget?.nama}" dari layanan ini?`} onConfirm={handleDetach} onCancel={() => setDetachTarget(null)} />
    </div>
  );
}

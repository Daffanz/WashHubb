import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { createPenerimaan } from '../../../api/penerimaanStokOutlet';
import { getDistribusis, getDistribusi } from '../../../api/distribusiOutlet';
import PageHeader from '../../../components/ui/PageHeader';
import FormSelect from '../../../components/ui/FormSelect';
import FormActions from '../../../components/ui/FormActions';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import { formatQty } from '../../../utils/format';

export default function PenerimaanOutletForm() {
  const navigate = useNavigate();
  const [distribusiList, setDistribusiList] = useState([]);
  const [selectedDistribusiId, setSelectedDistribusiId] = useState('');
  const [distribusi, setDistribusi] = useState(null);
  const [qtyMap, setQtyMap] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(false);

  useEffect(() => {
    getDistribusis({ per_page: 100 }).then((res) => {
      const filtered = (res.data.data || []).filter((d) => d.status?.kode === 'dikirim' || d.status?.kode === 'dikirim_sebagian');
      setDistribusiList(filtered.map((d) => ({
        value: d.id,
        label: `#${d.id} - ${d.permintaan?.outlet || '-'} (${d.tanggal_kirim})`,
      })));
    }).catch(() => {});
  }, []);

  useEffect(() => {
    if (!selectedDistribusiId) {
      setDistribusi(null);
      setQtyMap({});
      return;
    }

    setFetching(true);
    getDistribusi(selectedDistribusiId)
      .then((res) => {
        const data = res.data.data;
        setDistribusi(data);
        // Pre-fill qty with jumlah_kirim
        const map = {};
        data.details?.forEach((d) => { map[d.id] = d.jumlah_kirim; });
        setQtyMap(map);
      })
      .catch(() => toast.error('Gagal memuat detail distribusi'))
      .finally(() => setFetching(false));
  }, [selectedDistribusiId]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    const items = [];
    for (const d of distribusi.details) {
      const qty = qtyMap[d.id];
      if (!qty || parseFloat(qty) <= 0) {
        const name = d.tipe_item === 'mesin' ? d.mesin?.nama : d.bahan_baku?.nama;
        toast.error(`Qty diterima untuk "${name}" wajib diisi`);
        setLoading(false);
        return;
      }
      items.push({ distribusi_outlet_detail_id: d.id, qty_diterima: parseFloat(qty) });
    }

    try {
      await createPenerimaan({ distribusi_outlet_id: parseInt(selectedDistribusiId), items });
      toast.success('Penerimaan berhasil dicatat. Stok outlet bertambah.');
      navigate('/operasional/penerimaan-stok-outlet');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Gagal menyimpan penerimaan');
    }
    setLoading(false);
  };

  return (
    <div>
      <PageHeader title="Buat Penerimaan Stok Outlet" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Penerimaan Stok Outlet', to: '/operasional/penerimaan-stok-outlet' }, { label: 'Buat' }]} />
      <form onSubmit={handleSubmit}>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
          <FormSelect label="Distribusi Outlet" value={selectedDistribusiId} onChange={(e) => setSelectedDistribusiId(e.target.value)} options={distribusiList} required placeholder="Pilih distribusi" />
        </div>

        {fetching ? (
          <LoadingSpinner />
        ) : distribusi && (
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <h3 className="text-sm font-semibold text-gray-900 mb-4">Items dari Distribusi #{distribusi.id}</h3>
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm">
                <thead><tr className="border-b">
                  <th className="text-left py-2 text-gray-500">Tipe</th>
                  <th className="text-left py-2 text-gray-500">Item</th>
                  <th className="text-right py-2 text-gray-500">Jumlah Kirim</th>
                  <th className="text-right py-2 text-gray-500">Qty Diterima</th>
                </tr></thead>
                <tbody>
                  {distribusi.details?.map((d) => (
                    <tr key={d.id} className="border-b last:border-0">
                      <td className="py-2">
                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${d.tipe_item === 'mesin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}`}>
                          {d.tipe_item === 'mesin' ? 'Mesin' : 'Bahan Baku'}
                        </span>
                      </td>
                      <td className="py-2 font-medium">{d.tipe_item === 'mesin' ? d.mesin?.nama : d.bahan_baku?.nama || '-'}</td>
                      <td className="py-2 text-right">{formatQty(d.jumlah_kirim)}</td>
                      <td className="py-2 text-right">
                        <input
                          type="number"
                          step="any"
                          min="0"
                          max={d.jumlah_kirim}
                          value={qtyMap[d.id] || ''}
                          onChange={(e) => setQtyMap({ ...qtyMap, [d.id]: e.target.value })}
                          className="w-24 px-2 py-1 text-sm border border-gray-300 rounded text-right"
                          placeholder="0"
                        />
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        <FormActions loading={loading} />
      </form>
    </div>
  );
}

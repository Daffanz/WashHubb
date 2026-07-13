import React, { useState, useEffect, useCallback } from 'react';
import toast from 'react-hot-toast';
import { getMutations } from '../../api/mutations';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import { formatDateTime, formatQty } from '../../utils/format';

export default function MutationList() {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [type, setType] = useState('bahan');

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try { const res = await getMutations({ page: p, type }); setData(res.data.data); setMeta(res.data.meta); }
    catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page, type]);

  useEffect(() => { setPage(1); fetchData(1); }, [type]);

  const columns = [
    { label: 'Item', render: (r) => <span className="font-medium">{r.item_nama || '-'}</span> },
    { label: 'Jenis', render: (r) => {
      const colors = { masuk: 'text-emerald-600', keluar: 'text-red-600' };
      return <span className={`font-medium capitalize ${colors[r.jenis_mutasi] || ''}`}>{r.jenis_mutasi}</span>;
    }},
    { label: 'Jumlah', render: (r) => <span className="font-semibold">{formatQty(r.jumlah)}</span> },
    { label: 'Penerimaan', render: (r) => r.penerimaan ? (
      <span className="text-xs">
        <span className="font-mono text-wash-700">{r.penerimaan.nomor_penerimaan}</span>
        <br /><span className="text-gray-400">PO: {r.penerimaan.nomor_po}</span>
      </span>
    ) : <span className="text-gray-400">-</span> },
    { label: 'Tanggal', render: (r) => formatDateTime(r.tanggal) },
  ];

  return (
    <div>
      <PageHeader title="Mutasi Barang" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Inventory' }, { label: 'Mutasi' }]} actionLabel="Buat Mutasi" actionTo="/inventory/mutations/create" />
      <div className="mb-4 flex gap-2">
        <button onClick={() => setType('bahan')} className={`px-4 py-2 text-sm font-medium rounded-lg transition ${type === 'bahan' ? 'bg-wash-900 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'}`}>Bahan Baku</button>
        <button onClick={() => setType('mesin')} className={`px-4 py-2 text-sm font-medium rounded-lg transition ${type === 'mesin' ? 'bg-wash-900 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'}`}>Mesin</button>
      </div>
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)} />
    </div>
  );
}

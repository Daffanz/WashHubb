import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getSupplierStock } from '../../api/supplierStocks';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatQty, formatDateTime } from '../../utils/format';

export default function SupplierStockDetail() {
  const { id } = useParams();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getSupplierStock(id)
      .then((res) => setData(res.data.data))
      .catch(() => toast.error('Gagal memuat data'))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <LoadingSpinner />;
  if (!data) return null;

  return (
    <div>
      <PageHeader
        title={`Detail Stok: ${data.item?.nama || ''}`}
        breadcrumbs={[
          { label: 'Home', to: '/' },
          { label: 'Stok Supplier', to: '/suppliers/stock' },
          { label: 'Detail' },
        ]}
      />

      {/* Ringkasan */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
          <p className="text-xs text-gray-500 mb-1">Stok Masuk</p>
          <p className="text-2xl font-bold text-emerald-600">{formatQty(data.stok_masuk)}</p>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
          <p className="text-xs text-gray-500 mb-1">Stok Keluar</p>
          <p className="text-2xl font-bold text-red-600">{formatQty(data.stok_keluar)}</p>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
          <p className="text-xs text-gray-500 mb-1">Stok Saat Ini</p>
          <p className="text-2xl font-bold text-wash-800">{formatQty(data.stok_saat_ini)}</p>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
          <p className="text-xs text-gray-500 mb-1">Item</p>
          <p className="text-sm font-medium">{data.item?.nama}</p>
          {data.item?.satuan && <p className="text-xs text-gray-400">{data.item.satuan}</p>}
          {data.item?.kategori && <p className="text-xs text-gray-400">{data.item.kategori}</p>}
        </div>
      </div>

      {/* Riwayat */}
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div className="px-6 py-4 border-b border-gray-100">
          <h3 className="text-sm font-semibold text-gray-900">Riwayat Stok</h3>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jenis</th>
                <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Keterangan</th>
                <th className="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Dikirim</th>
                <th className="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Diterima</th>
                <th className="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Cacat</th>
                <th className="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Retur</th>
                <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tujuan</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {data.riwayat?.length === 0 ? (
                <tr><td colSpan={8} className="px-4 py-8 text-center text-sm text-gray-400">Belum ada riwayat</td></tr>
              ) : data.riwayat?.map((r, i) => (
                <tr key={i} className="hover:bg-gray-50/50">
                  <td className="px-4 py-3 text-sm text-gray-600">{formatDateTime(r.tanggal)}</td>
                  <td className="px-4 py-3">
                    <span className={`text-xs font-medium px-2 py-0.5 rounded ${r.jenis === 'masuk' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}`}>
                      {r.jenis === 'masuk' ? 'Masuk' : 'Keluar'}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-sm font-medium">{r.keterangan}</td>
                  <td className="px-4 py-3 text-sm text-right font-medium">{r.jenis === 'keluar' ? formatQty(r.jumlah_kirim) : '-'}</td>
                  <td className="px-4 py-3 text-sm text-right font-medium text-emerald-600">{r.diterima > 0 ? formatQty(r.diterima) : '-'}</td>
                  <td className="px-4 py-3 text-sm text-right font-medium text-red-600">{r.cacat > 0 ? formatQty(r.cacat) : '-'}</td>
                  <td className="px-4 py-3 text-sm text-right font-medium text-amber-600">{r.retur > 0 ? formatQty(r.retur) : '-'}</td>
                  <td className="px-4 py-3 text-sm text-gray-600">{r.tujuan}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

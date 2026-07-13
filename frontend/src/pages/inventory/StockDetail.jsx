import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getStock } from '../../api/stocks';
import PageHeader from '../../components/ui/PageHeader';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatQty } from '../../utils/format';

export default function StockDetail() {
  const { type, id } = useParams();
  const [stock, setStock] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getStock(type, id).then((res) => setStock(res.data.data)).catch(() => toast.error('Gagal memuat data')).finally(() => setLoading(false));
  }, [type, id]);

  if (loading) return <LoadingSpinner />;
  if (!stock) return null;

  const isBahan = type === 'bahan';
  const itemName = isBahan ? stock.bahan_baku?.nama : stock.mesin?.nama;

  return (
    <div>
      <PageHeader title={`Detail Stok: ${itemName || ''}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Stok', to: '/inventory/stocks' }, { label: 'Detail' }]} />
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-lg">
        <dl className="space-y-3 text-sm">
          <div className="flex justify-between"><dt className="text-gray-500">Tipe</dt><dd className="capitalize">{isBahan ? 'Bahan Baku' : 'Mesin'}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Nama</dt><dd className="font-medium">{itemName}</dd></div>
          {isBahan && <>
            <div className="flex justify-between"><dt className="text-gray-500">Kategori</dt><dd>{stock.bahan_baku?.kategori || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Satuan</dt><dd>{stock.bahan_baku?.satuan || '-'}</dd></div>
          </>}
          {!isBahan && <>
            <div className="flex justify-between"><dt className="text-gray-500">Kode</dt><dd>{stock.mesin?.kode_mesin || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Merk</dt><dd>{stock.mesin?.merk || '-'}</dd></div>
          </>}
          <div className="flex justify-between pt-2 border-t"><dt className="text-gray-900 font-semibold">Stok Saat Ini</dt><dd className="text-xl font-bold text-wash-800">{formatQty(stock.stok_saat_ini)}</dd></div>
        </dl>
      </div>
    </div>
  );
}

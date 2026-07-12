import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getSupplier } from '../../api/suppliers';
import PageHeader from '../../components/ui/PageHeader';
import StatusBadge from '../../components/ui/StatusBadge';
import LoadingSpinner from '../../components/ui/LoadingSpinner';
import { formatRupiah } from '../../utils/format';

export default function SupplierDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [supplier, setSupplier] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getSupplier(id).then((res) => setSupplier(res.data.data)).catch(() => { toast.error('Gagal memuat data'); navigate(-1); }).finally(() => setLoading(false));
  }, [id, navigate]);

  if (loading) return <LoadingSpinner />;
  if (!supplier) return null;

  return (
    <div>
      <PageHeader title={`Supplier: ${supplier.user?.name || '-'}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Suppliers', to: '/suppliers' }, { label: 'Detail' }]} />
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Informasi Supplier</h3>
          <dl className="space-y-3 text-sm">
            <div className="flex justify-between"><dt className="text-gray-500">Nama</dt><dd className="font-medium">{supplier.user?.name || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Email</dt><dd>{supplier.user?.email || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Jenis</dt><dd className="capitalize">{supplier.jenis_supplier?.replace('_', ' ')}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Alamat</dt><dd>{supplier.alamat || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Telepon</dt><dd>{supplier.telepon || '-'}</dd></div>
            <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd><StatusBadge status={supplier.status} /></dd></div>
          </dl>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Bahan Baku yang Disuplai</h3>
          {supplier.bahan_bakus?.length > 0 ? (
            <table className="min-w-full text-sm">
              <thead><tr className="border-b"><th className="text-left py-2 text-gray-500">Nama</th><th className="text-left py-2 text-gray-500">Satuan</th><th className="text-right py-2 text-gray-500">Harga</th></tr></thead>
              <tbody>
                {supplier.bahan_bakus.map((b) => (
                  <tr key={b.id} className="border-b last:border-0">
                    <td className="py-2 font-medium">{b.nama}</td>
                    <td className="py-2 text-gray-600">{b.satuan}</td>
                    <td className="py-2 text-right">{formatRupiah(b.harga_standar)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          ) : <p className="text-sm text-gray-400">Belum ada bahan baku</p>}
        </div>
      </div>
    </div>
  );
}

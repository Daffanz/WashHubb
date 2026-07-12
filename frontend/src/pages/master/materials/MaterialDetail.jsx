import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getMaterial } from '../../../api/materials';
import PageHeader from '../../../components/ui/PageHeader';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import { formatRupiah } from '../../../utils/format';

export default function MaterialDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [material, setMaterial] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getMaterial(id).then((res) => setMaterial(res.data.data)).catch(() => { toast.error('Gagal memuat data'); navigate(-1); }).finally(() => setLoading(false));
  }, [id, navigate]);

  if (loading) return <LoadingSpinner />;
  if (!material) return null;

  return (
    <div>
      <PageHeader title={`Bahan Baku: ${material.nama}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Data Master', to: '/master/materials' }, { label: 'Detail' }]} />
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-lg">
        <dl className="space-y-3 text-sm">
          <div className="flex justify-between"><dt className="text-gray-500">Nama</dt><dd className="font-medium">{material.nama}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Kategori</dt><dd>{material.kategori?.nama || '-'}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Satuan</dt><dd>{material.satuan}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Harga Standar</dt><dd className="font-semibold text-wash-800">{formatRupiah(material.harga_standar)}</dd></div>
        </dl>
      </div>
    </div>
  );
}

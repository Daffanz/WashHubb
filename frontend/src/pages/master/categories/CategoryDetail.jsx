import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getCategory } from '../../../api/categories';
import PageHeader from '../../../components/ui/PageHeader';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';

export default function CategoryDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [category, setCategory] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getCategory(id).then((res) => setCategory(res.data.data)).catch(() => { toast.error('Gagal memuat data'); navigate(-1); }).finally(() => setLoading(false));
  }, [id, navigate]);

  if (loading) return <LoadingSpinner />;
  if (!category) return null;

  return (
    <div>
      <PageHeader title={`Kategori: ${category.nama}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Data Master', to: '/master/categories' }, { label: 'Detail' }]} />
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-lg">
        <dl className="space-y-3 text-sm">
          <div className="flex justify-between"><dt className="text-gray-500">Nama</dt><dd className="font-medium">{category.nama}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Jumlah Bahan Baku</dt><dd>{category.bahan_bakus_count ?? 0}</dd></div>
        </dl>
      </div>
    </div>
  );
}

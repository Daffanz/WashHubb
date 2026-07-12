import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getMachine } from '../../../api/machines';
import PageHeader from '../../../components/ui/PageHeader';
import LoadingSpinner from '../../../components/ui/LoadingSpinner';
import { formatRupiah } from '../../../utils/format';

export default function MachineDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [machine, setMachine] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getMachine(id).then((res) => setMachine(res.data.data)).catch(() => { toast.error('Gagal memuat data'); navigate(-1); }).finally(() => setLoading(false));
  }, [id, navigate]);

  if (loading) return <LoadingSpinner />;
  if (!machine) return null;

  return (
    <div>
      <PageHeader title={`Mesin: ${machine.nama}`} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Data Master', to: '/master/machines' }, { label: 'Detail' }]} />
      <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-lg">
        <dl className="space-y-3 text-sm">
          <div className="flex justify-between"><dt className="text-gray-500">Nama</dt><dd className="font-medium">{machine.nama}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Kode Mesin</dt><dd className="font-mono">{machine.kode_mesin || '-'}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Merk</dt><dd>{machine.merk || '-'}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Tipe</dt><dd>{machine.tipe || '-'}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Kapasitas</dt><dd>{machine.kapasitas ? `${machine.kapasitas} kg` : '-'}</dd></div>
          <div className="flex justify-between"><dt className="text-gray-500">Harga Standar</dt><dd className="font-semibold text-wash-800">{formatRupiah(machine.harga_standar)}</dd></div>
        </dl>
      </div>
    </div>
  );
}

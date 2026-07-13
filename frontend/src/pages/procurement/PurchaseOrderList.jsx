import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import PageHeader from '../../components/ui/PageHeader';
import DataTable from '../../components/ui/DataTable';
import StatusBadge from '../../components/ui/StatusBadge';
import ConfirmModal from '../../components/ui/ConfirmModal';
import { formatRupiah } from '../../utils/format';
import { kirimPurchaseOrder } from '../../api/purchaseOrders';
import { useAuth } from '../../hooks/useAuth';

export default function PurchaseOrderList() {
  const { user } = useAuth();
  const isSupplier = user?.role?.kode === 'supplier';
  const canCreatePO = user?.role?.kode === 'procurement' || user?.role?.kode === 'admin_it';
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleting, setDeleting] = useState(false);

  const fetchData = useCallback(async (p = page) => {
    setLoading(true);
    try {
      const res = await fetch(`/api/procurement/purchase-orders?page=${p}`, { headers: { Authorization: `Bearer ${localStorage.getItem('washhub_token')}` } });
      const d = await res.json();
      setData(d.data); setMeta(d.meta);
    } catch { toast.error('Gagal memuat data'); }
    setLoading(false);
  }, [page]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const handleDelete = async () => {
    setDeleting(true);
    try {
      await fetch(`/api/procurement/purchase-orders/${deleteTarget.id}`, { method: 'DELETE', headers: { Authorization: `Bearer ${localStorage.getItem('washhub_token')}` } });
      toast.success('PO berhasil dihapus'); setDeleteTarget(null); fetchData();
    } catch { toast.error('Gagal menghapus PO'); }
    setDeleting(false);
  };

  // UC-30: Kirim PO ke Supplier
  const handleKirim = async (po) => {
    try {
      await kirimPurchaseOrder(po.id);
      toast.success('PO berhasil dikirim ke supplier');
      fetchData();
    } catch (err) { toast.error(err.response?.data?.message || 'Gagal mengirim PO'); }
  };

  const columns = [
    { label: 'Nomor PO', render: (r) => <span className="font-mono font-medium text-wash-800">{r.nomor_po}</span> },
    { label: 'Supplier', render: (r) => r.supplier?.nama || '-' },
    { label: 'Dibuat Oleh', render: (r) => r.dibuat_oleh?.nama || '-' },
    { label: 'Jenis', render: (r) => <span className="capitalize">{r.jenis_po?.replace('_', ' ')}</span> },
    { label: 'Total', render: (r) => <span className="font-semibold">{formatRupiah(r.total_nilai)}</span> },
    { label: 'Status', render: (r) => <StatusBadge status={r.status?.label} color={
      r.status?.kode === 'selesai' ? 'green' : r.status?.kode === 'ditolak' ? 'red' :
      r.status?.kode === 'disetujui' ? 'green' : r.status?.kode === 'dikirim' ? 'blue' : 'yellow'
    } /> },
  ];

  return (
    <div>
      <PageHeader title="Purchase Orders" breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Pengadaan' }, { label: 'PO' }]} actionLabel={canCreatePO ? 'Buat PO' : null} actionTo="/procurement/purchase-orders/create" />
      <DataTable columns={columns} data={data} loading={loading} meta={meta} onPageChange={(p) => setPage(p)}
        actions={(row) => (
          <div className="flex gap-1 justify-end flex-wrap">
            <Link to={`/procurement/purchase-orders/${row.id}`} className="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50">Detail</Link>
            {!isSupplier && row.status?.kode === 'diajukan' && (
              <>
                <Link to={`/procurement/purchase-orders/${row.id}/edit`} className="text-xs text-wash-700 hover:text-wash-900 font-medium px-2 py-1 rounded hover:bg-wash-50">Edit</Link>
                {/* UC-30: Kirim ke Supplier (hanya Tim Pengadaan) */}
                <button onClick={() => handleKirim(row)} className="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50">Kirim</button>
                <button onClick={() => setDeleteTarget(row)} className="text-xs text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50">Hapus</button>
              </>
            )}
          </div> 
        )}
      />
      <ConfirmModal open={!!deleteTarget} title="Hapus PO" message={`Yakin ingin menghapus PO "${deleteTarget?.nomor_po}"?`} onConfirm={handleDelete} onCancel={() => setDeleteTarget(null)} loading={deleting} />
    </div>
  );
}

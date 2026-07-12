import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getSupplier, createSupplier, updateSupplier } from '../../api/suppliers';
import { getUsers } from '../../api/users';
import { getMaterials } from '../../api/materials';
import { getMachines } from '../../api/machines';
import PageHeader from '../../components/ui/PageHeader';
import FormInput from '../../components/ui/FormInput';
import FormSelect from '../../components/ui/FormSelect';
import FormTextarea from '../../components/ui/FormTextarea';
import FormGrid from '../../components/ui/FormGrid';
import FormActions from '../../components/ui/FormActions';
import { JENIS_SUPPLIER_OPTIONS } from '../../utils/constants';

export default function SupplierForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;

  const [form, setForm] = useState({ user_id: '', jenis_supplier: '', alamat: '', katalog_produk: '' });
  const [selectedItems, setSelectedItems] = useState([]);
  const [users, setUsers] = useState([]);
  const [allMaterials, setAllMaterials] = useState([]);
  const [allMachines, setAllMachines] = useState([]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEdit);

  useEffect(() => {
    Promise.all([
      getUsers({ per_page: 100 }),
      getMaterials({ per_page: 100 }),
      getMachines({ per_page: 100 }),
    ]).then(([uRes, mRes, mcRes]) => {
      const supplierUsers = uRes.data.data
        .filter((u) => u.role?.kode === 'supplier')
        .map((u) => ({ value: u.id, label: `${u.nama} (${u.email})` }));
      setUsers(supplierUsers);
      setAllMaterials(mRes.data.data);
      setAllMachines(mcRes.data.data);
    });
    if (isEdit) {
      getSupplier(id).then((res) => {
        const s = res.data.data;
        setForm({ user_id: s.user?.id || '', jenis_supplier: s.jenis_supplier || '', alamat: s.alamat || '', katalog_produk: s.katalog_produk || '' });
        setSelectedItems(s.bahan_bakus?.map((b) => b.id) || s.mesins?.map((m) => m.id) || []);
      }).catch(() => toast.error('Gagal memuat data')).finally(() => setFetching(false));
    }
  }, [id, isEdit]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm(prev => ({ ...prev, [name]: value }));
    if (name === 'jenis_supplier') setSelectedItems([]);
  };

  const toggleItem = (itemId) => {
    setSelectedItems((prev) => prev.includes(itemId) ? prev.filter((i) => i !== itemId) : [...prev, itemId]);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const itemField = form.jenis_supplier === 'mesin' ? 'mesin_ids' : 'bahan_baku_ids';
      const payload = { ...form, [itemField]: selectedItems };
      if (isEdit) { await updateSupplier(id, payload); toast.success('Supplier berhasil diperbarui'); }
      else { await createSupplier(payload); toast.success('Supplier berhasil dibuat'); }
      navigate('/suppliers');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error(err.response?.data?.message || 'Terjadi kesalahan');
    }
    setLoading(false);
  };

  if (fetching) return <div className="flex justify-center py-16"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>;

  const isBahan = form.jenis_supplier !== 'mesin';
  const itemList = isBahan ? allMaterials : allMachines;

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit Supplier' : 'Tambah Supplier'} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Suppliers', to: '/suppliers' }, { label: isEdit ? 'Edit' : 'Tambah' }]} />
      <form onSubmit={handleSubmit}>
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Informasi Supplier</h3>
          <FormGrid>
            <FormSelect label="User" name="user_id" value={form.user_id} onChange={handleChange} options={users} error={errors.user_id?.[0]} required placeholder="Pilih user" disabled={isEdit} />
            <FormSelect label="Jenis Supplier" name="jenis_supplier" value={form.jenis_supplier} onChange={handleChange} options={JENIS_SUPPLIER_OPTIONS} error={errors.jenis_supplier?.[0]} required placeholder="Pilih jenis" disabled={isEdit} />
            <div className="md:col-span-2">
              <FormTextarea label="Alamat" name="alamat" value={form.alamat} onChange={handleChange} error={errors.alamat?.[0]} required placeholder="Masukkan alamat lengkap" />
            </div>
            <div className="md:col-span-2">
              <FormTextarea label="Katalog Produk" name="katalog_produk" value={form.katalog_produk} onChange={handleChange} error={errors.katalog_produk?.[0]} placeholder="Daftar produk yang disuplai (opsional)" rows={2} />
            </div>
          </FormGrid>
        </div>

        {form.jenis_supplier && (
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <h3 className="text-sm font-semibold text-gray-900 mb-1">
              {isBahan ? 'Bahan Baku yang Disuplai' : 'Mesin yang Disuplai'}
            </h3>
            <p className="text-xs text-gray-500 mb-4">
              {isBahan ? 'Pilih bahan-baku yang menjadi produk supplier ini' : 'Pilih mesin yang menjadi produk supplier ini'}
            </p>
            {itemList.length === 0 ? (
              <p className="text-sm text-gray-400">
                {isBahan ? 'Belum ada data bahan baku.' : 'Belum ada data mesin.'}
                Buat dulu di menu Data Master.
              </p>
            ) : (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                {itemList.map((item) => (
                  <label key={item.id} className={`flex items-center gap-3 px-4 py-3 rounded-lg border cursor-pointer transition ${selectedItems.includes(item.id) ? 'border-wash-500 bg-wash-50 ring-1 ring-wash-200' : 'border-gray-200 hover:bg-gray-50'}`}>
                    <input type="checkbox" checked={selectedItems.includes(item.id)} onChange={() => toggleItem(item.id)} className="rounded border-gray-300 text-wash-600 focus:ring-wash-500" />
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-gray-900 truncate">{item.nama}</p>
                      {isBahan ? (
                        <p className="text-xs text-gray-500">{item.satuan} · Rp {Number(item.harga_standar).toLocaleString('id-ID')}</p>
                      ) : (
                        <p className="text-xs text-gray-500">{item.kode_mesin} · {item.merk || '-'}</p>
                      )}
                    </div>
                  </label>
                ))}
              </div>
            )}
            {errors.bahan_baku_ids && <p className="mt-2 text-xs text-red-600">{errors.bahan_baku_ids[0]}</p>}
            {errors.mesin_ids && <p className="mt-2 text-xs text-red-600">{errors.mesin_ids[0]}</p>}
          </div>
        )}

        <FormActions loading={loading} />
      </form>
    </div>
  );
}

import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { getJadwalShift, createJadwalShift, updateJadwalShift } from '../../../api/jadwalShift';
import { getOutlets } from '../../../api/outlets';
import { getUsers } from '../../../api/users';
import PageHeader from '../../../components/ui/PageHeader';
import FormSelect from '../../../components/ui/FormSelect';
import FormInput from '../../../components/ui/FormInput';
import FormActions from '../../../components/ui/FormActions';
import { HARI_OPTIONS } from '../../../utils/constants';

export default function ShiftForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;
  const [outletId, setOutletId] = useState('');
  const [mingguMulai, setMingguMulai] = useState('');
  const [outlets, setOutlets] = useState([]);
  const [users, setUsers] = useState([]);
  const [shifts, setShifts] = useState([{ user_id: '', hari: 'senin', jam_mulai: '08:00', jam_selesai: '16:00' }]);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(isEdit);

  useEffect(() => {
    getOutlets({ per_page: 100 }).then((res) => setOutlets(res.data.data.map((o) => ({ value: o.id, label: o.nama }))));
    getUsers({ per_page: 100 }).then((res) => setUsers(res.data.data.map((u) => ({ value: u.id, label: u.nama }))));
    if (isEdit) {
      getJadwalShift(id).then((res) => {
        const j = res.data.data;
        setOutletId(j.outlet?.id || '');
        setMingguMulai(j.minggu_mulai || '');
        setShifts(j.details?.length > 0 ? j.details.map((d) => ({ user_id: d.user?.id || '', hari: d.hari, jam_mulai: d.jam_mulai, jam_selesai: d.jam_selesai })) : [{ user_id: '', hari: 'senin', jam_mulai: '08:00', jam_selesai: '16:00' }]);
      }).catch(() => toast.error('Gagal memuat data')).finally(() => setFetching(false));
    }
  }, [id, isEdit]);

  const addShift = () => setShifts([...shifts, { user_id: '', hari: 'senin', jam_mulai: '08:00', jam_selesai: '16:00' }]);
  const removeShift = (i) => setShifts(shifts.filter((_, idx) => idx !== i));
  const updateShift = (i, field, val) => { const n = [...shifts]; n[i][field] = val; setShifts(n); };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setErrors({});
    try {
      const validShifts = shifts.filter((s) => s.user_id && s.hari && s.jam_mulai && s.jam_selesai);
      if (validShifts.length === 0) { toast.error('Tambah minimal 1 shift'); setLoading(false); return; }
      const payload = { shifts: validShifts };
      if (!isEdit) { payload.outlet_id = parseInt(outletId); payload.minggu_mulai = mingguMulai; }
      if (isEdit) { await updateJadwalShift(id, payload); toast.success('Jadwal shift berhasil diperbarui'); }
      else { await createJadwalShift(payload); toast.success('Jadwal shift berhasil dibuat'); }
      navigate('/operasional/jadwal-shift');
    } catch (err) {
      if (err.response?.status === 422) setErrors(err.response.data.errors || {});
      else toast.error(err.response?.data?.message || 'Terjadi kesalahan');
    }
    setLoading(false);
  };

  if (fetching) return <div className="flex justify-center py-16"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-wash-900"></div></div>;

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit Jadwal Shift' : 'Buat Jadwal Shift'} breadcrumbs={[{ label: 'Home', to: '/' }, { label: 'Shift', to: '/operasional/jadwal-shift' }, { label: isEdit ? 'Edit' : 'Buat' }]} />
      <form onSubmit={handleSubmit}>
        {!isEdit && (
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              <FormSelect label="Outlet" name="outlet_id" value={outletId} onChange={(e) => setOutletId(e.target.value)} options={outlets} error={errors.outlet_id?.[0]} required placeholder="Pilih outlet" />
              <FormInput label="Minggu Mulai" name="minggu_mulai" type="date" value={mingguMulai} onChange={(e) => setMingguMulai(e.target.value)} error={errors.minggu_mulai?.[0]} required />
            </div>
          </div>
        )}

        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-sm font-semibold text-gray-900">Shift</h3>
            <button type="button" onClick={addShift} className="text-sm text-wash-700 hover:text-wash-900 font-medium">+ Tambah Shift</button>
          </div>
          <div className="space-y-3">
            {shifts.map((s, i) => (
              <div key={i} className="grid grid-cols-1 md:grid-cols-5 gap-3 items-end p-3 bg-gray-50 rounded-lg">
                <FormSelect label={i === 0 ? 'Staf' : ''} value={s.user_id} onChange={(e) => updateShift(i, 'user_id', e.target.value)} options={users} placeholder="Pilih staf" />
                <FormSelect label={i === 0 ? 'Hari' : ''} value={s.hari} onChange={(e) => updateShift(i, 'hari', e.target.value)} options={HARI_OPTIONS} />
                <FormInput label={i === 0 ? 'Jam Mulai' : ''} type="time" value={s.jam_mulai} onChange={(e) => updateShift(i, 'jam_mulai', e.target.value)} />
                <FormInput label={i === 0 ? 'Jam Selesai' : ''} type="time" value={s.jam_selesai} onChange={(e) => updateShift(i, 'jam_selesai', e.target.value)} />
                <div className={i === 0 ? 'pt-5' : ''}>{shifts.length > 1 && <button type="button" onClick={() => removeShift(i)} className="text-sm text-red-600 hover:text-red-800">Hapus</button>}</div>
              </div>
            ))}
          </div>
        </div>

        <FormActions loading={loading} />
      </form>
    </div>
  );
}

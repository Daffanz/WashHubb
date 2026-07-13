<?php
namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            // Modul 1
            ['konteks' => 'akun', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'akun', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'supplier', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'supplier', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'stok_supplier', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'stok_supplier', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],

            // Modul 2
            ['konteks' => 'bahan_baku', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'bahan_baku', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'jenis_layanan', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'jenis_layanan', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'mesin', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'mesin', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],

            // Modul 3 — PO (sesuai alur.md line 13-18)
            ['konteks' => 'purchase_order', 'kode' => 'diajukan', 'label' => 'Diajukan'],
            ['konteks' => 'purchase_order', 'kode' => 'disetujui', 'label' => 'Disetujui'],
            ['konteks' => 'purchase_order', 'kode' => 'disetujui_sebagian', 'label' => 'Disetujui Sebagian'],
            ['konteks' => 'purchase_order', 'kode' => 'ditolak', 'label' => 'Ditolak'],
            ['konteks' => 'purchase_order', 'kode' => 'dikirim', 'label' => 'Dikirim'],
            ['konteks' => 'purchase_order', 'kode' => 'selesai', 'label' => 'Selesai'],

            // PO Item (sesuai alur.md line 19-21)
            ['konteks' => 'purchase_order_item', 'kode' => 'disetujui', 'label' => 'Disetujui'],
            ['konteks' => 'purchase_order_item', 'kode' => 'disetujui_sebagian', 'label' => 'Disetujui Sebagian'],
            ['konteks' => 'purchase_order_item', 'kode' => 'ditolak', 'label' => 'Ditolak'],

            // Distribusi (sesuai alur.md line 22-24)
            ['konteks' => 'distribusi_barang', 'kode' => 'dikirim', 'label' => 'Dikirim'],
            ['konteks' => 'distribusi_barang', 'kode' => 'dikirim_sebagian', 'label' => 'Dikirim Sebagian'],
            ['konteks' => 'distribusi_barang', 'kode' => 'diterima', 'label' => 'Diterima'],

            // Retur (sesuai alur.md line 25-27)
            ['konteks' => 'retur_barang', 'kode' => 'menunggu_pengganti', 'label' => 'Menunggu Pengganti'],
            ['konteks' => 'retur_barang', 'kode' => 'pengganti_dikirim', 'label' => 'Pengganti Dikirim'],
            ['konteks' => 'retur_barang', 'kode' => 'selesai', 'label' => 'Selesai'],

            // Penerimaan
            ['konteks' => 'penerimaan_barang', 'kode' => 'draft', 'label' => 'Draft'],
            ['konteks' => 'penerimaan_barang', 'kode' => 'menunggu_retur', 'label' => 'Menunggu Retur'],
            ['konteks' => 'penerimaan_barang', 'kode' => 'selesai', 'label' => 'Selesai'],

            // Stok pusat
            ['konteks' => 'stok_pusat', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'stok_pusat', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],

            // Detail mesin
            ['konteks' => 'detail_mesin', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'detail_mesin', 'kode' => 'digunakan', 'label' => 'Digunakan'],
            ['konteks' => 'detail_mesin', 'kode' => 'maintenance', 'label' => 'Maintenance'],
            ['konteks' => 'detail_mesin', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],

            // Outlet
            ['konteks' => 'outlet', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'outlet', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],

            // Modul 5.1 — Order Cucian
            ['konteks' => 'order_cucian', 'kode' => 'diproses', 'label' => 'Diproses'],
            ['konteks' => 'order_cucian', 'kode' => 'selesai', 'label' => 'Selesai'],
            ['konteks' => 'order_cucian', 'kode' => 'dibatalkan', 'label' => 'Dibatalkan'],

            // Modul 5.2 — Permintaan Stok Outlet (header rollup)
            ['konteks' => 'permintaan_stok_outlet', 'kode' => 'diajukan', 'label' => 'Diajukan'],
            ['konteks' => 'permintaan_stok_outlet', 'kode' => 'disetujui', 'label' => 'Disetujui'],
            ['konteks' => 'permintaan_stok_outlet', 'kode' => 'disetujui_sebagian', 'label' => 'Disetujui Sebagian'],
            ['konteks' => 'permintaan_stok_outlet', 'kode' => 'ditolak', 'label' => 'Ditolak'],

            // Modul 5.2 — Permintaan Stok Outlet Detail
            ['konteks' => 'permintaan_stok_outlet_detail', 'kode' => 'diajukan', 'label' => 'Diajukan'],
            ['konteks' => 'permintaan_stok_outlet_detail', 'kode' => 'disetujui', 'label' => 'Disetujui'],
            ['konteks' => 'permintaan_stok_outlet_detail', 'kode' => 'disetujui_sebagian', 'label' => 'Disetujui Sebagian'],
            ['konteks' => 'permintaan_stok_outlet_detail', 'kode' => 'ditolak', 'label' => 'Ditolak'],

            // Modul 5.2 — Distribusi Outlet
            ['konteks' => 'distribusi_outlet', 'kode' => 'dikirim', 'label' => 'Dikirim'],
            ['konteks' => 'distribusi_outlet', 'kode' => 'dikirim_sebagian', 'label' => 'Dikirim Sebagian'],
            ['konteks' => 'distribusi_outlet', 'kode' => 'diterima', 'label' => 'Diterima'],

            // Modul 5.4 — Jadwal Service Mesin
            ['konteks' => 'jadwal_service_mesin', 'kode' => 'menunggu_persetujuan', 'label' => 'Menunggu Persetujuan'],
            ['konteks' => 'jadwal_service_mesin', 'kode' => 'ditolak', 'label' => 'Ditolak'],
            ['konteks' => 'jadwal_service_mesin', 'kode' => 'disetujui', 'label' => 'Disetujui'],
            ['konteks' => 'jadwal_service_mesin', 'kode' => 'selesai', 'label' => 'Selesai'],

            // Modul 5.5 — Jadwal Shift Staf
            ['konteks' => 'jadwal_shift_staf', 'kode' => 'belum_berjalan', 'label' => 'Belum Berjalan'],
            ['konteks' => 'jadwal_shift_staf', 'kode' => 'berjalan', 'label' => 'Berjalan'],
            ['konteks' => 'jadwal_shift_staf', 'kode' => 'selesai', 'label' => 'Selesai'],

            // Modul 5.5 — Jadwal Shift Staf Detail (per karyawan per hari)
            ['konteks' => 'jadwal_shift_staf_detail', 'kode' => 'belum_berjalan', 'label' => 'Belum Berjalan'],
            ['konteks' => 'jadwal_shift_staf_detail', 'kode' => 'berjalan', 'label' => 'Berjalan'],
            ['konteks' => 'jadwal_shift_staf_detail', 'kode' => 'selesai', 'label' => 'Selesai'],

            // Modul 6.2 — Loyalti
            ['konteks' => 'loyalti', 'kode' => 'menunggu_evaluasi', 'label' => 'Menunggu Evaluasi'],
            ['konteks' => 'loyalti', 'kode' => 'memenuhi_target', 'label' => 'Memenuhi Target'],
            ['konteks' => 'loyalti', 'kode' => 'tidak_memenuhi_target', 'label' => 'Tidak Memenuhi Target'],
            ['konteks' => 'loyalti', 'kode' => 'menunggu_pencairan', 'label' => 'Menunggu Pencairan'],
            ['konteks' => 'loyalti', 'kode' => 'diproses_pencairan', 'label' => 'Diproses Pencairan'],
            ['konteks' => 'loyalti', 'kode' => 'selesai', 'label' => 'Selesai'],

            // Modul 6.2 — Loyalti Pencairan
            ['konteks' => 'loyalti_pencairan', 'kode' => 'diproses', 'label' => 'Diproses'],
            ['konteks' => 'loyalti_pencairan', 'kode' => 'selesai', 'label' => 'Selesai'],
            ['konteks' => 'loyalti_pencairan', 'kode' => 'gagal', 'label' => 'Gagal'],
        ];

        foreach ($statuses as $s) {
            Status::firstOrCreate(['konteks' => $s['konteks'], 'kode' => $s['kode']], ['label' => $s['label']]);
        }
    }
}

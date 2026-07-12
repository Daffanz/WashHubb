<?php
namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['konteks' => 'akun', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'akun', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'supplier', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'supplier', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'stok_supplier', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'stok_supplier', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'bahan_baku', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'bahan_baku', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'jenis_layanan', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'jenis_layanan', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'mesin', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'mesin', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'purchase_order', 'kode' => 'diajukan', 'label' => 'Diajukan'],
            ['konteks' => 'purchase_order', 'kode' => 'disetujui', 'label' => 'Disetujui'],
            ['konteks' => 'purchase_order', 'kode' => 'disetujui_sebagian', 'label' => 'Disetujui Sebagian'],
            ['konteks' => 'purchase_order', 'kode' => 'ditolak', 'label' => 'Ditolak'],
            ['konteks' => 'purchase_order', 'kode' => 'dikirim', 'label' => 'Dikirim'],
            ['konteks' => 'purchase_order', 'kode' => 'selesai', 'label' => 'Selesai'],
            ['konteks' => 'purchase_order_item', 'kode' => 'disetujui', 'label' => 'Disetujui'],
            ['konteks' => 'purchase_order_item', 'kode' => 'disetujui_sebagian', 'label' => 'Disetujui Sebagian'],
            ['konteks' => 'purchase_order_item', 'kode' => 'ditolak', 'label' => 'Ditolak'],
            ['konteks' => 'distribusi_barang', 'kode' => 'dikirim', 'label' => 'Dikirim'],
            ['konteks' => 'distribusi_barang', 'kode' => 'dikirim_sebagian', 'label' => 'Dikirim Sebagian'],
            ['konteks' => 'distribusi_barang', 'kode' => 'diterima', 'label' => 'Diterima'],
            ['konteks' => 'retur_barang', 'kode' => 'menunggu_pengganti', 'label' => 'Menunggu Pengganti'],
            ['konteks' => 'retur_barang', 'kode' => 'pengganti_dikirim', 'label' => 'Pengganti Dikirim'],
            ['konteks' => 'retur_barang', 'kode' => 'selesai', 'label' => 'Selesai'],
            ['konteks' => 'stok_pusat', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'stok_pusat', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'detail_mesin', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'detail_mesin', 'kode' => 'digunakan', 'label' => 'Digunakan'],
            ['konteks' => 'detail_mesin', 'kode' => 'maintenance', 'label' => 'Maintenance'],
            ['konteks' => 'detail_mesin', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
            ['konteks' => 'outlet', 'kode' => 'aktif', 'label' => 'Aktif'],
            ['konteks' => 'outlet', 'kode' => 'nonaktif', 'label' => 'Nonaktif'],
        ];

        foreach ($statuses as $s) {
            Status::firstOrCreate(['konteks' => $s['konteks'], 'kode' => $s['kode']], ['label' => $s['label']]);
        }
    }
}

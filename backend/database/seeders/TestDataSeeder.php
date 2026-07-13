<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Status;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\KategoriBahanBaku;
use App\Models\BahanBaku;
use App\Models\JenisLayanan;
use App\Models\JenisLayananBahanBaku;
use App\Models\Mesin;
use App\Models\Franchise;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $aktifStatus = Status::where('konteks', 'akun')->where('kode', 'aktif')->first();
        $aktifSupplier = Status::where('konteks', 'supplier')->where('kode', 'aktif')->first();
        $aktifBahan = Status::where('konteks', 'bahan_baku')->where('kode', 'aktif')->first();
        $aktifLayanan = Status::where('konteks', 'jenis_layanan')->where('kode', 'aktif')->first();
        $aktifMesin = Status::where('konteks', 'mesin')->where('kode', 'aktif')->first();

        // ==========================================
        // USERS — 1 per role
        // ==========================================
        $franchisor = User::firstOrCreate(['email' => 'franchisor@washhub.com'], [
            'nama' => 'Franchisor WashHub', 'password' => Hash::make('password'),
            'no_telp' => '081111111111', 'role_id' => Role::where('kode', 'franchisor')->first()->id,
            'status_id' => $aktifStatus?->id,
        ]);

        $procurement = User::firstOrCreate(['email' => 'procurement@washhub.com'], [
            'nama' => 'Tim Pengadaan', 'password' => Hash::make('password'),
            'no_telp' => '082222222222', 'role_id' => Role::where('kode', 'procurement')->first()->id,
            'status_id' => $aktifStatus?->id,
        ]);

        $supplierBB = User::firstOrCreate(['email' => 'supplier_bb@washhub.com'], [
            'nama' => 'Supplier Bahan Baku Jaya', 'password' => Hash::make('password'),
            'no_telp' => '083333333333', 'role_id' => Role::where('kode', 'supplier')->first()->id,
            'status_id' => $aktifStatus?->id,
        ]);

        $supplierMesin = User::firstOrCreate(['email' => 'supplier_ms@washhub.com'], [
            'nama' => 'Supplier Mesin Abadi', 'password' => Hash::make('password'),
            'no_telp' => '084444444444', 'role_id' => Role::where('kode', 'supplier')->first()->id,
            'status_id' => $aktifStatus?->id,
        ]);

        $managerOutlet = User::firstOrCreate(['email' => 'manager@washhub.com'], [
            'nama' => 'Manager Outlet Utama', 'password' => Hash::make('password'),
            'no_telp' => '085555555555', 'role_id' => Role::where('kode', 'manager_outlet')->first()->id,
            'status_id' => $aktifStatus?->id,
        ]);

        // ==========================================
        // FRANCHISE USERS — 3 franchise
        // ==========================================
        $franchiseRole = Role::where('kode', 'franchise')->first();

        $franchise1 = User::firstOrCreate(['email' => 'franchise1@washhub.com'], [
            'nama' => 'Franchise Bandung', 'password' => Hash::make('password'),
            'no_telp' => '086666666661', 'role_id' => $franchiseRole->id,
            'status_id' => $aktifStatus?->id,
        ]);
        Franchise::firstOrCreate(['user_id' => $franchise1->id]);

        $franchise2 = User::firstOrCreate(['email' => 'franchise2@washhub.com'], [
            'nama' => 'Franchise Surabaya', 'password' => Hash::make('password'),
            'no_telp' => '086666666662', 'role_id' => $franchiseRole->id,
            'status_id' => $aktifStatus?->id,
        ]);
        Franchise::firstOrCreate(['user_id' => $franchise2->id]);

        $franchise3 = User::firstOrCreate(['email' => 'franchise3@washhub.com'], [
            'nama' => 'Franchise Jakarta', 'password' => Hash::make('password'),
            'no_telp' => '086666666663', 'role_id' => $franchiseRole->id,
            'status_id' => $aktifStatus?->id,
        ]);
        Franchise::firstOrCreate(['user_id' => $franchise3->id]);

        // ==========================================
        // SUPPLIER PROFILES
        // ==========================================
        $supBB = Supplier::firstOrCreate(['user_id' => $supplierBB->id], [
            'jenis_supplier' => 'bahan_baku', 'alamat' => 'Jl. Industri No. 10, Bandung',
            'katalog_produk' => 'Deterjen, Pewangi, Pelicin, Softener', 'status_id' => $aktifSupplier?->id,
        ]);

        $supMesin = Supplier::firstOrCreate(['user_id' => $supplierMesin->id], [
            'jenis_supplier' => 'mesin', 'alamat' => 'Jl. Teknik No. 25, Surabaya',
            'katalog_produk' => 'Mesin Cuci, Mesin Pengering, Setrika Uap', 'status_id' => $aktifSupplier?->id,
        ]);

        // ==========================================
        // KATEGORI BAHAN BAKU
        // ==========================================
        $katDeterjen = KategoriBahanBaku::firstOrCreate(['nama' => 'Deterjen']);
        $katPewangi = KategoriBahanBaku::firstOrCreate(['nama' => 'Pewangi']);
        $katSoftener = KategoriBahanBaku::firstOrCreate(['nama' => 'Softener']);
        $katPelicin = KategoriBahanBaku::firstOrCreate(['nama' => 'Pelicin']);
        $katPembersih = KategoriBahanBaku::firstOrCreate(['nama' => 'Pembersih']);

        // ==========================================
        // BAHAN BAKU
        // ==========================================
        $rinso = BahanBaku::firstOrCreate(['nama' => 'Rinso', 'kategori_id' => $katDeterjen->id], [
            'satuan' => 'kg', 'harga_standar' => 25000, 'status_id' => $aktifBahan?->id,
        ]);
        $molto = BahanBaku::firstOrCreate(['nama' => 'Molto', 'kategori_id' => $katPewangi->id], [
            'satuan' => 'ml', 'harga_standar' => 15000, 'status_id' => $aktifBahan?->id,
        ]);
        $downy = BahanBaku::firstOrCreate(['nama' => 'Downy', 'kategori_id' => $katSoftener->id], [
            'satuan' => 'ml', 'harga_standar' => 18000, 'status_id' => $aktifBahan?->id,
        ]);
        $wings = BahanBaku::firstOrCreate(['nama' => 'Wings Biru', 'kategori_id' => $katPelicin->id], [
            'satuan' => 'pcs', 'harga_standar' => 5000, 'status_id' => $aktifBahan?->id,
        ]);
        $vanish = BahanBaku::firstOrCreate(['nama' => 'Vanish', 'kategori_id' => $katPembersih->id], [
            'satuan' => 'gram', 'harga_standar' => 12000, 'status_id' => $aktifBahan?->id,
        ]);

        // ==========================================
        // KONEKSI SUPPLIER ↔ BAHAN BAKU
        // ==========================================
        $supBB->bahanBakus()->syncWithoutDetaching([$rinso->id, $molto->id, $downy->id, $wings->id, $vanish->id]);

        // ==========================================
        // MESIN (MASTER)
        // ==========================================
        $samsung = Mesin::firstOrCreate(['kode_mesin' => 'MC-SAM-001'], [
            'nama' => 'Samsung Front Load', 'merk' => 'Samsung', 'tipe' => 'Front Load', 'kapasitas' => 10, 'harga_standar' => 8500000, 'status_id' => $aktifMesin?->id,
        ]);
        $lg = Mesin::firstOrCreate(['kode_mesin' => 'MC-LG-001'], [
            'nama' => 'LG Top Load', 'merk' => 'LG', 'tipe' => 'Top Load', 'kapasitas' => 8, 'harga_standar' => 6500000, 'status_id' => $aktifMesin?->id,
        ]);
        $speedQueen = Mesin::firstOrCreate(['kode_mesin' => 'MC-SQ-001'], [
            'nama' => 'Speed Queen Washer', 'merk' => 'Speed Queen', 'tipe' => 'Commercial', 'kapasitas' => 15, 'harga_standar' => 15000000, 'status_id' => $aktifMesin?->id,
        ]);
        $samsungDryer = Mesin::firstOrCreate(['kode_mesin' => 'MD-SAM-001'], [
            'nama' => 'Samsung Dryer', 'merk' => 'Samsung', 'tipe' => 'Front Load', 'kapasitas' => 10, 'harga_standar' => 7500000, 'status_id' => $aktifMesin?->id,
        ]);

        // ==========================================
        // KONEKSI SUPPLIER ↔ MESIN
        // ==========================================
        $supMesin->mesins()->syncWithoutDetaching([$samsung->id, $lg->id, $speedQueen->id, $samsungDryer->id]);

        // ==========================================
        // JENIS LAYANAN
        // ==========================================
        $cuciKering = JenisLayanan::firstOrCreate(['nama' => 'Cuci Kering'], [
            'harga_standar_per_kg' => 8000, 'status_id' => $aktifLayanan?->id,
        ]);
        $cuciSetrika = JenisLayanan::firstOrCreate(['nama' => 'Cuci Setrika'], [
            'harga_standar_per_kg' => 12000, 'status_id' => $aktifLayanan?->id,
        ]);
        $cuciExpress = JenisLayanan::firstOrCreate(['nama' => 'Cuci Express'], [
            'harga_standar_per_kg' => 15000, 'status_id' => $aktifLayanan?->id,
        ]);

        // ==========================================
        // JENIS LAYANAN ↔ BAHAN BAKU (komposisi)
        // ==========================================
        // Cuci Kering: Deterjen 10g/kg + Pewangi 5ml/kg
        JenisLayananBahanBaku::firstOrCreate(
            ['jenis_layanan_id' => $cuciKering->id, 'bahan_baku_id' => $rinso->id],
            ['jumlah_konsumsi' => 10]
        );
        JenisLayananBahanBaku::firstOrCreate(
            ['jenis_layanan_id' => $cuciKering->id, 'bahan_baku_id' => $molto->id],
            ['jumlah_konsumsi' => 5]
        );

        // Cuci Setrika: Deterjen 10g/kg + Pewangi 5ml/kg + Softener 3ml/kg
        JenisLayananBahanBaku::firstOrCreate(
            ['jenis_layanan_id' => $cuciSetrika->id, 'bahan_baku_id' => $rinso->id],
            ['jumlah_konsumsi' => 10]
        );
        JenisLayananBahanBaku::firstOrCreate(
            ['jenis_layanan_id' => $cuciSetrika->id, 'bahan_baku_id' => $molto->id],
            ['jumlah_konsumsi' => 5]
        );
        JenisLayananBahanBaku::firstOrCreate(
            ['jenis_layanan_id' => $cuciSetrika->id, 'bahan_baku_id' => $downy->id],
            ['jumlah_konsumsi' => 3]
        );

        // Cuci Express: Deterjen 12g/kg + Pewangi 5ml/kg
        JenisLayananBahanBaku::firstOrCreate(
            ['jenis_layanan_id' => $cuciExpress->id, 'bahan_baku_id' => $rinso->id],
            ['jumlah_konsumsi' => 12]
        );
        JenisLayananBahanBaku::firstOrCreate(
            ['jenis_layanan_id' => $cuciExpress->id, 'bahan_baku_id' => $molto->id],
            ['jumlah_konsumsi' => 5]
        );

        // ==========================================
        // STOK SUPPLIER (Bahan Baku)
        // ==========================================
        $aktifStok = Status::where('konteks', 'stok_supplier')->where('kode', 'aktif')->first();
        foreach ([$rinso, $molto, $downy, $wings, $vanish] as $bahan) {
            \App\Models\StokSupplierBahanBaku::firstOrCreate(
                ['supplier_id' => $supBB->id, 'bahan_baku_id' => $bahan->id],
                ['stok_saat_ini' => 100, 'status_id' => $aktifStok?->id]
            );
        }

        // ==========================================
        // STOK SUPPLIER (Mesin)
        // ==========================================
        foreach ([$samsung, $lg, $speedQueen, $samsungDryer] as $mesin) {
            \App\Models\StokSupplierMesin::firstOrCreate(
                ['supplier_id' => $supMesin->id, 'mesin_id' => $mesin->id],
                ['stok_saat_ini' => 5, 'status_id' => $aktifStok?->id]
            );
        }

        $this->command->info('✅ Test data berhasil dibuat!');
        $this->command->info('Users:');
        $this->command->info('  franchisor@washhub.com / password (Franchisor)');
        $this->command->info('  procurement@washhub.com / password (Tim Pengadaan)');
        $this->command->info('  supplier_bb@washhub.com / password (Supplier Bahan Baku)');
        $this->command->info('  supplier_ms@washhub.com / password (Supplier Mesin)');
        $this->command->info('  manager@washhub.com / password (Manager Outlet)');

        // ==========================================
        // PO BAHAN BAKU (diajukan)
        // ==========================================
        $diajukan = Status::where('konteks', 'purchase_order')->where('kode', 'diajukan')->first();
        $itemDiajukan = Status::where('konteks', 'purchase_order_item')->where('kode', 'disetujui')->first();

        $poBB = \App\Models\PurchaseOrder::create([
            'supplier_id' => $supBB->id,
            'dibuat_oleh_id' => $procurement->id,
            'jenis_po' => 'bahan_baku',
            'total_nilai' => (100 * 25000) + (50 * 15000),
            'status_id' => $diajukan?->id,
        ]);
        \App\Models\PurchaseOrderItemBahanBaku::create(['po_id' => $poBB->id, 'bahan_baku_id' => $rinso->id, 'jumlah' => 100, 'harga_satuan' => 25000, 'status_id' => $itemDiajukan?->id]);
        \App\Models\PurchaseOrderItemBahanBaku::create(['po_id' => $poBB->id, 'bahan_baku_id' => $molto->id, 'jumlah' => 50, 'harga_satuan' => 15000, 'status_id' => $itemDiajukan?->id]);

        // ==========================================
        // PO MESIN (diajukan)
        // ==========================================
        $poMesin = \App\Models\PurchaseOrder::create([
            'supplier_id' => $supMesin->id,
            'dibuat_oleh_id' => $procurement->id,
            'jenis_po' => 'mesin',
            'total_nilai' => (2 * 8500000) + (1 * 6500000),
            'status_id' => $diajukan?->id,
        ]);
        \App\Models\PurchaseOrderItemMesin::create(['po_id' => $poMesin->id, 'mesin_id' => $samsung->id, 'jumlah' => 2, 'harga_satuan' => 8500000, 'status_id' => $itemDiajukan?->id]);
        \App\Models\PurchaseOrderItemMesin::create(['po_id' => $poMesin->id, 'mesin_id' => $lg->id, 'jumlah' => 1, 'harga_satuan' => 6500000, 'status_id' => $itemDiajukan?->id]);

        $this->command->info('PO:');
        $this->command->info("  {$poBB->nomor_po} - Bahan Baku (Rinso 100kg + Molto 50ml) - Status: diajukan");
        $this->command->info("  {$poMesin->nomor_po} - Mesin (Samsung 2 + LG 1) - Status: diajukan");
    }
}

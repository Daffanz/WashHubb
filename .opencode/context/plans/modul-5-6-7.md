# Plan: Implementasi Modul 5, 6, 7 — Operasional, Franchise, Dashboard

**Status:** ready
**Created:** 2026-07-13

---

## TL;DR

Implementasi 3 modul: Operasional Laundry (5), Manajemen Franchise (6), Dashboard Monitoring (7). Mengikuti pola yang sudah ada di codebase (Modul 1-4). Tidak mengubah file yang sudah berjalan.

---

## Analisis Codebase Saat Ini

### Pola yang Sudah Ada (WAJIB diikuti):

| Aspek | Pola |
|---|---|
| **Model** | `$fillable`, `casts()`, relationship `BelongsTo`/`HasMany` |
| **Controller** | `index()` (paginated), `store()` (validate+create), `show()`, `update()`, `destroy()` |
| **Response** | `response()->json(['data' => ..., 'meta' => [...]])` |
| **Route** | Group prefix, `permission:` middleware |
| **Frontend API** | `src/api/{module}.js` — axios wrapper |
| **Frontend Page** | `List.jsx` (DataTable), `Form.jsx` (form), `Detail.jsx` (detail) |
| **Status** | Table `statuses` dengan `konteks`/`kode`/`label` |
| **Stock** | Via `StockService` — `decreaseSupplierStockFromReceipt`, `increaseCompanyStockFromReceipt` |

### Tabel yang Sudah Ada:

| Tabel | Modul | Status |
|---|---|---|
| `franchises` | 6.1 | ✅ Ada |
| `outlets` | 6.1 | ✅ Ada |
| `user_outlets` | 5.1 | ✅ Ada |
| `detail_mesins` | 5.1, 5.4 | ✅ Ada (punya outlet_id, status_id) |
| `jenis_layanans` | 5.1 | ✅ Ada |
| `jenis_layanan_bahan_bakus` | 5.1 | ✅ Ada (punya konsumsi_per_kg) |
| `mesins` | master | ✅ Ada |
| `bahan_bakus` | master | ✅ Ada |
| `stok_pusat_bahan_bakus` | 5.2 | ✅ Ada |
| `stok_pusat_mesins` | 5.2 | ✅ Ada |

### Tabel yang BELUM Ada (perlu dibuat):

#### Modul 5:
- `order_cucians` — 5.1
- `stok_outlet_bahan_bakus` — 5.2
- `mutasi_stok_outlet_bahan_bakus` — 5.2
- `permintaan_stok_outlets` + `permintaan_stok_outlet_details` — 5.2
- `distribusi_outlets` + `distribusi_outlet_details` — 5.2
- `penerimaan_outlets` + `penerimaan_outlet_details` — 5.2
- `jadwal_service_mesins` — 5.4
- `jadwal_shift_stafs` + `jadwal_shift_staf_details` — 5.5

#### Modul 6:
- `loyaltis` — 6.2
- `loyalti_pencairans` — 6.2

---

## Execution Waves

### Wave 1: Migration + Seeder (Foundation)

| Task | Deskripsi | File |
|---|---|---|
| 1.1 | Migration: order_cucians | `database/migrations/...` |
| 1.2 | Migration: stok_outlet + mutasi_stok_outlet | `database/migrations/...` |
| 1.3 | Migration: permintaan_stok_outlet + detail | `database/migrations/...` |
| 1.4 | Migration: distribusi_outlet + detail | `database/migrations/...` |
| 1.5 | Migration: penerimaan_outlet + detail | `database/migrations/...` |
| 1.6 | Migration: jadwal_service_mesins | `database/migrations/...` |
| 1.7 | Migration: jadwal_shift_staf + detail | `database/migrations/...` |
| 1.8 | Migration: loyalti + loyalti_pencairan | `database/migrations/...` |
| 1.9 | Seeder: tambah status entries baru | `StatusSeeder.php` |

### Wave 2: Models (Backend Foundation)

| Task | Deskripsi | File |
|---|---|---|
| 2.1 | Model OrderCucian | `app/Models/OrderCucian.php` |
| 2.2 | Model StokOutletBahanBaku + MutasiStokOutletBahanBaku | `app/Models/...` |
| 2.3 | Model PermintaanStokOutlet + Detail | `app/Models/...` |
| 2.4 | Model DistribusiOutlet + Detail | `app/Models/...` |
| 2.5 | Model PenerimaanOutlet + Detail | `app/Models/...` |
| 2.6 | Model JadwalServiceMesin | `app/Models/JadwalServiceMesin.php` |
| 2.7 | Model JadwalShiftStaf + Detail | `app/Models/...` |
| 2.8 | Model Loyalti + LoyaltiPencairan | `app/Models/...` |

### Wave 3: Controllers (Backend API)

| Task | Deskripsi | File |
|---|---|---|
| 3.1 | OrderCucianController (5.1) | `app/Http/Controllers/Api/Operasional/OrderCucianController.php` |
| 3.2 | PermintaanStokOutletController (5.2) | `app/Http/Controllers/Api/Operasional/PermintaanStokController.php` |
| 3.3 | DistribusiOutletController (5.2) | `app/Http/Controllers/Api/Operasional/DistribusiOutletController.php` |
| 3.4 | JadwalServiceController (5.4) | `app/Http/Controllers/Api/Operasional/JadwalServiceController.php` |
| 3.5 | JadwalShiftController (5.5) | `app/Http/Controllers/Api/Operasional/JadwalShiftController.php` |
| 3.6 | OutletController (6.1) | `app/Http/Controllers/Api/Franchise/OutletController.php` |
| 3.7 | LoyaltiController (6.2) | `app/Http/Controllers/Api/Franchise/LoyaltiController.php` |
| 3.8 | DashboardController (7.x) | `app/Http/Controllers/Api/Dashboard/DashboardController.php` |

### Wave 4: Routes

| Task | Deskripsi | File |
|---|---|---|
| 4.1 | Tambah route Modul 5, 6, 7 | `routes/api.php` |

### Wave 5: Frontend API + Pages

| Task | Deskripsi | File |
|---|---|---|
| 5.1 | API files (orderCucian, permintaanStok, distribusiOutlet, jadwalService, jadwalShift, outlets, loyalti, dashboard) | `src/api/...` |
| 5.2 | Pages: OrderCucian (List, Form, Detail) | `src/pages/operasional/orders/` |
| 5.3 | Pages: PermintaanStok (List, Form, Detail) | `src/pages/operasional/permintaan/` |
| 5.4 | Pages: JadwalService (List, Form, Detail) | `src/pages/operasional/service/` |
| 5.5 | Pages: JadwalShift (List, Form, Detail) | `src/pages/operasional/shift/` |
| 5.6 | Pages: Outlet (List, Form, Detail) | `src/pages/franchise/outlets/` |
| 5.7 | Pages: Loyalti (List, Detail) | `src/pages/franchise/loyalti/` |
| 5.8 | Pages: Dashboard (5 variants) | `src/pages/dashboard/` |

---

## Detail Tabel

### 5.1 order_cucians
```
id, outlet_id (FK outlets), user_id (FK users), jenis_layanan_id (FK jenis_layanans),
detail_mesin_id (FK detail_mesins), berat (decimal), total_harga (decimal),
waktu_masuk (timestamp), estimasi_selesai (timestamp), waktu_selesai (timestamp nullable),
alasan_pembatalan (text nullable), status_id (FK statuses), timestamps
```

### 5.2 stok_outlet_bahan_bakus
```
id, outlet_id (FK outlets), bahan_baku_id (FK bahan_bakus),
stok_saat_ini (decimal), stok_minimum (decimal), stok_masuk (decimal), stok_keluar (decimal),
timestamps, unique(outlet_id, bahan_baku_id)
```

### 5.2 mutasi_stok_outlet_bahan_bakus
```
id, stok_outlet_bahan_baku_id (FK), jenis_mutasi (string), jumlah (decimal),
tanggal (timestamp), order_cucian_id (FK nullable), penerimaan_outlet_detail_id (FK nullable),
timestamps
```

### 5.2 permintaan_stok_outlets
```
id, outlet_id (FK outlets), user_id (FK users), tanggal (date), status_id (FK), timestamps
```

### 5.2 permintaan_stok_outlet_details
```
id, permintaan_stok_outlet_id (FK), bahan_baku_id (FK), jumlah_diminta (decimal),
jumlah_disetujui (decimal nullable), alasan (text nullable), status_id (FK), timestamps
```

### 5.2 distribusi_outlets
```
id, permintaan_stok_outlet_id (FK), tanggal_kirim (date), status_id (FK), timestamps
```

### 5.2 distribusi_outlet_details
```
id, distribusi_outlet_id (FK), bahan_baku_id (FK), jumlah_kirim (decimal), timestamps
```

### 5.2 penerimaan_outlets
```
id, distribusi_outlet_id (FK), user_id (FK), tanggal_terima (date), timestamps
```

### 5.2 penerimaan_outlet_details
```
id, penerimaan_outlet_id (FK), distribusi_outlet_detail_id (FK), qty_diterima (decimal), timestamps
```

### 5.4 jadwal_service_mesins
```
id, detail_mesin_id (FK), outlet_id (FK outlets), user_id (FK users yang mengajukan),
tanggal_pengajuan (date), tanggal_service (date nullable), deskripsi (text),
menunggu_mesin_bebas (boolean default false), status_id (FK), timestamps
```

### 5.5 jadwal_shift_stafs
```
id, outlet_id (FK), user_id (FK pembuat), minggu_mulai (date), status_id (FK), timestamps
```

### 5.5 jadwal_shift_staf_details
```
id, jadwal_shift_staf_id (FK), user_id (FK staf), hari (enum: senin-minggu),
jam_mulai (time), jam_selesai (time), timestamps
```

### 6.2 loyaltis
```
id, outlet_id (FK), periode (string, e.g. "2026-07"), target_omset (decimal),
target_operasional (decimal), omset_aktual (decimal default 0), capaian_operasional (decimal nullable),
memenuhi_target (boolean default false), jumlah_bonus (decimal nullable), keterangan (text nullable),
status_id (FK), timestamps
```

### 6.2 loyalti_pencairans
```
id, loyalti_id (FK), bukti_transfer (string nullable), status_id (FK), timestamps
```

---

## Status Entries Baru (untuk Seeder)

```
order_cucian: diproses, selesai, dibatalkan
permintaan_stok_outlet: diajukan, disetujui, disetujui_sebagian, ditolak
permintaan_stok_outlet_detail: diajukan, disetujui, disetujui_sebagian, ditolak
distribusi_outlet: dikirim, dikirim_sebagian, diterima
jadwal_service_mesin: menunggu_persetujuan, ditolak, disetujui, selesai
jadwal_shift_staf: belum_berjalan, berjalan, selesai
loyalti: menunggu_evaluasi, memenuhi_target, tidak_memenuhi_target, menunggu_pencairan, diproses_pencairan, selesai
loyalti_pencairan: diproses, selesai, gagal
```

---

## Key Business Rules (dari PRD)

### Module 5.1 — Order Cucian:
- Hanya mesin `status_id = aktif` di outlet tsb yang bisa dipilih
- Stok outlet berkurang otomatis: `konsumsi_per_kg × berat`
- Status mesin: `aktif → digunakan` saat order dibuat
- Penyelesaian: cek jadwal_service `disetujui` + `menunggu_mesin_bebas` → mesin ke maintenance atau aktif
- Pembatalan: kembalikan stok + cek mesin sama seperti selesai

### Module 5.2 — Permintaan Stok:
- Rollup status otomatis dari detail
- Multi-batch distribusi sampai `jumlah_disetujui` terpenuhi
- Stok pusat berkurang + stok outlet bertambah SAAT konfirmasi penerimaan (bukan saat kirim)

### Module 5.4 — Jadwal Service:
- Auto-tolak jika mesin `maintenance`/`nonaktif`
- Jika mesin `digunakan` → `menunggu_mesin_bebas = true`

### Module 6.2 — Loyalti:
- `omset_aktual` dihitung otomatis dari `order_cucian`
- Konfirmasi bonus oleh Franchisee (bukan Franchisor)
- Retry pencairan = baris baru di `loyalti_pencairans`

---

## Catatan Penting

1. **JANGAN ubah file yang sudah berjalan** — Modul 1-4 tetap utuh
2. **Ikuti pola existing** — nama file, struktur controller, response format
3. **StockService** — tambah method baru untuk outlet stock, jangan ubah yang ada
4. **Migration numbering** — lanjutkan dari `2026_07_13_...`
5. **PHP version issue** — migration tidak bisa dijalankan di lokal (PHP 8.1 vs 8.4), tapi code ditulis benar

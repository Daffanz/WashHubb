# PRD: Modul 5, 6, 7 — Operasional Laundry, Manajemen Franchise, Dashboard Monitoring

## 1. Latar Belakang & Ruang Lingkup
PRD ini merapikan spesifikasi Modul 5 (Operasional Laundry), Modul 6 (Manajemen Franchise), dan Modul 7 (Dashboard Monitoring) berdasarkan skema tabel dan use case yang sudah ditulis, dengan menambahkan: status enum eksplisit per entitas, aturan rollup status, urutan pemicu antar tabel, dan beberapa gap/inkonsistensi skema yang perlu diputuskan sebelum implementasi.

> **Prinsip yang dilanjutkan dari PRD Modul 3–4 (Pengadaan):** stok hanya berubah sebagai efek dari event yang **sudah dikonfirmasi diterima**, bukan saat barang dikirim. Prinsip ini juga berlaku konsisten di modul distribusi outlet (5.2) dan mutasi stok pusat.

## 2. Aktor per Modul

| Aktor | Modul yang digunakan |
|---|---|
| **Manajer Outlet** | 5.1 (order cucian), 5.2 (ajukan permintaan stok), 5.3 (riwayat), 5.4 (ajukan service), 5.5–5.6 (shift staf), 7.5 (dashboard) |
| **Tim Pengadaan** | 5.2 (validasi & kirim), 5.3 (riwayat distribusi), 7.2 (dashboard) |
| **Franchisor** | 5.4 (validasi service), 6.1 (kelola outlet), 6.2 (tetapkan target & cairkan bonus), 7.1 (dashboard) |
| **Franchisee** | 6.1 (lihat outlet read-only), 6.2 (konfirmasi bonus), 7.4 (dashboard) |
| **Supplier** | 7.3 (dashboard) — sudah dibahas penuh di PRD Modul 3–4 |

---

## 3. MODUL 5 — Operasional Laundry

### 5.1 Proses Cucian (`order_cucian`)

**Status enum `order_cucian.status_id`:** `diproses` → `selesai` | `dibatalkan`

**Alur:**
1. Manajer Outlet membuat `order_cucian`, memilih `jenis_layanan_id` dan `detail_mesin_id` — hanya mesin dengan `status_id = aktif` di outlet tersebut yang boleh dipilih. Tidak ada mesin aktif tersedia → tolak dengan pesan error.
2. Sistem set `waktu_masuk` = now, `estimasi_selesai` dihitung dari estimasi waktu proses `jenis_layanan`.
3. Sistem mengurangi `stok_outlet_bahan_baku` sesuai `jenis_layanan_bahan_baku.konsumsi_per_kg × berat`, dicatat sebagai `mutasi_stok_outlet_bahan_baku` (`jenis_mutasi = keluar`, `order_cucian_id` terisi).
4. `detail_mesin.status_id`: `aktif` → `digunakan`.
5. Order bisa diedit selama `status ≠ selesai`.
6. **Penyelesaian order** (`status → selesai`, set `waktu_selesai`): cek apakah ada `jadwal_service_mesin` untuk mesin yang sama dengan `status_id = disetujui` dan `menunggu_mesin_bebas = true` → jika ya, `detail_mesin.status_id → maintenance`; jika tidak → `→ aktif`.
7. **Pembatalan** (`status → dibatalkan`, `alasan_pembatalan` wajib): kembalikan `stok_outlet_bahan_baku` (mutasi `jenis_mutasi = keluar` dibalik/dibatalkan), lalu jalankan pengecekan mesin yang sama seperti poin 6.

> Poin 6 dan 7 berbagi logika yang sama ("cek mesin sebelum dikembalikan ke aktif") — sebaiknya diimplementasikan sebagai satu fungsi/service bersama `resolveMachineStatusAfterOrderClose(detail_mesin_id)`, dipanggil dari kedua alur, supaya tidak ada duplikasi logika yang bisa divergen.

### 5.2 Permintaan Stok Bahan Baku (Outlet → Pengadaan)

**Status enum:**
| Tabel | Status |
|---|---|
| `permintaan_stok_outlet` | `diajukan` → (rollup, lihat di bawah) |
| `permintaan_stok_outlet_detail` | `diajukan` → `disetujui` \| `disetujui_sebagian` \| `ditolak` |
| `distribusi_outlet` | `dikirim` → `dikirim_sebagian` → `diterima` |
| — | (tidak perlu `penerimaan_outlet.status_id` terpisah — lihat catatan 3 di bawah) |

**Alur:**
1. Manajer Outlet membuat `permintaan_stok_outlet` + detail per bahan baku. Status awal `diajukan`. Bisa dihapus manajer outlet **hanya** selama status = `diajukan` (belum divalidasi).
2. Tim Pengadaan memvalidasi **per item**: `disetujui` (penuh), `disetujui_sebagian` (+`alasan` wajib, `jumlah_disetujui` < `jumlah_diminta`), atau `ditolak` (+`alasan` wajib).
3. **Rollup `permintaan_stok_outlet.status_id`** dihitung otomatis dari status seluruh detail-nya (sama pola dengan rollup PO di PRD Pengadaan): semua `ditolak` → `ditolak`; semua `disetujui` penuh → `disetujui`; campuran → `disetujui_sebagian`.
4. Tim Pengadaan membuat `distribusi_outlet` (bisa lebih dari satu batch per `permintaan_stok_outlet` — lihat catatan 1) dengan `distribusi_outlet_detail.jumlah_kirim` ≤ sisa yang belum terkirim dari `jumlah_disetujui`. Status `distribusi_outlet` = `dikirim`.
5. Manajer Outlet mengonfirmasi via `penerimaan_outlet` (+ detail `qty_diterima`):
   - Jika `qty_diterima` = seluruh `jumlah_kirim` yang tersisa untuk item itu → `distribusi_outlet.status_id → diterima`.
   - Jika sebagian → status tetap `dikirim_sebagian`, menunggu batch `distribusi_outlet` berikutnya untuk sisa kekurangan.
6. **Setiap konfirmasi penerimaan** (bukan saat distribusi dikirim): sistem otomatis menambah `stok_outlet_bahan_baku` (`mutasi_stok_outlet_bahan_baku`, `jenis_mutasi = masuk`, `penerimaan_outlet_detail_id` terisi) **dan** mengurangi `stok_pusat_bahan_baku` (`mutasi_stok_pusat_bahan_baku`, `jenis_mutasi = keluar`).

**Catatan penting / gap skema yang perlu diputuskan:**

1. **Distribusi bisa multi-batch per permintaan.** Karena `distribusi_outlet.permintaan_stok_outlet_id` merujuk ke header (bukan ke detail per item), satu permintaan bisa punya beberapa `distribusi_outlet` berbeda pada tanggal berbeda sampai seluruh `jumlah_disetujui` per item terkirim. Sisa kirim per item dihitung sebagai: `jumlah_disetujui − SUM(jumlah_kirim dari seluruh distribusi_outlet_detail terkait item itu)`.
2. **Inkonsistensi FK di `mutasi_stok_pusat_bahan_baku`:** kolom yang tersedia adalah `distribusi_outlet_detail_id`, padahal aturan bisnis (poin 6 di atas) menyatakan stok pusat berkurang **saat konfirmasi penerimaan outlet**, bukan saat distribusi dikirim. Ini tidak konsisten dengan `mutasi_stok_outlet_bahan_baku` yang justru sudah benar memakai `penerimaan_outlet_detail_id`. **Rekomendasi:** ubah FK di `mutasi_stok_pusat_bahan_baku` menjadi referensi ke `penerimaan_outlet_detail_id`, supaya kedua mutasi (naik di outlet, turun di pusat) sama-sama dipicu oleh event konfirmasi yang sama, bukan oleh event pengiriman.
3. **`penerimaan_outlet` tidak butuh `status_id` sendiri** (berbeda dengan `penerimaan_barang` di modul pengadaan yang butuh status untuk proses retur) — karena di level outlet tidak ada proses retur, cukup rollup langsung ke `distribusi_outlet.status_id`.

### 5.3 Riwayat Permintaan & Distribusi
- Manajer Outlet: riwayat `permintaan_stok_outlet` miliknya (read-only) + unduh PDF.
- Tim Pengadaan: riwayat `distribusi_outlet` ke seluruh outlet, filter by tanggal/jenis item/tujuan outlet + unduh PDF.

### 5.4 Jadwal Service Mesin (`jadwal_service_mesin`)

**Status enum:** `menunggu_persetujuan` → `ditolak` | `disetujui` → `selesai`

**Alur:**
1. Manajer Outlet mengajukan. Sistem cek status mesin real-time:
   - `maintenance` atau `nonaktif` → sistem langsung menolak (`status → ditolak`, tanpa perlu Franchisor).
   - `aktif` atau `digunakan` → diterima ke antrean (`status → menunggu_persetujuan`); jika mesin sedang `digunakan`, tampilkan peringatan (bukan blokir).
2. Franchisor memvalidasi:
   - Tolak → `status → ditolak`.
   - Setuju → cek ulang status mesin **real-time** (bisa sudah berubah sejak diajukan):
     - `aktif` → langsung `detail_mesin.status_id → maintenance`, `jadwal_service_mesin.status → disetujui`.
     - `digunakan` → `jadwal_service_mesin.status → disetujui` DAN `menunggu_mesin_bebas = true`; `detail_mesin.status_id` **belum** berubah, ditunda sampai order cucian yang memakainya selesai/dibatalkan (lihat 5.1 poin 6–7).
3. Manajer Outlet menandai service selesai → `jadwal_service_mesin.status → selesai`, `detail_mesin.status_id`: `maintenance → aktif`.
4. Jadwal yang sudah dibuat **tidak bisa dihapus** (audit trail).

### 5.5 & 5.6 Jadwal Shift Staf

**Status enum `jadwal_shift_staf.status_id`:** `belum_berjalan` → `berjalan` → `selesai`
> Skema tidak menunjukkan siapa/apa yang memicu transisi `belum_berjalan → berjalan → selesai`. **Rekomendasi:** jalankan sebagai scheduled job harian yang membandingkan `minggu_mulai` dengan tanggal berjalan (`berjalan` saat mulai minggu itu, `selesai` saat minggu itu berakhir), bukan aksi manual.

- Manajer Outlet membuat jadwal mingguan + detail per staf/hari. Bisa diedit (tambah/hapus/simpan) **hanya** selama `belum_berjalan`.
- Riwayat (5.6): tampilkan jadwal berstatus `selesai`, unduh PDF.

---

## 4. MODUL 6 — Manajemen Franchise

### 6.1 Data Outlet
- Franchisor mendaftarkan outlet baru (set `franchise_id` pemilik) dan bisa mengubah datanya.
- Franchisee: read-only, dibatasi otomatis ke outlet miliknya sendiri (filter by `franchise_id`).
- Outlet tutup → `status_id → nonaktif` (soft, tidak dihapus — konsisten dengan pola soft-delete supplier di PRD Pengadaan).

### 6.2 Loyalti (Bonus Kinerja Mitra)

**Status enum `loyalti.status_id`:**
```
menunggu_evaluasi
  → memenuhi_target          (omset_aktual ≥ target_omset DAN capaian_operasional ≥ target_operasional)
  → tidak_memenuhi_target     (salah satu tidak terpenuhi — proses berhenti, terminal)
memenuhi_target
  → menunggu_pencairan        (Franchisor menetapkan jumlah_bonus + keterangan)
  → diproses_pencairan        (Franchisor mencairkan, upload bukti_transfer)
  → selesai                   (Franchisee konfirmasi "Diterima")
  → menunggu_pencairan        (Franchisee konfirmasi "Belum Diterima" — kembali untuk dicairkan ulang)
```

**Status enum `loyalti_pencairan.status_id`:** `diproses` → `selesai` | `gagal`

**Alur:**
1. Franchisor menetapkan `target_omset` + `target_operasional` per outlet per periode → `loyalti` dibuat, `status = menunggu_evaluasi`.
2. Franchisor input `capaian_operasional` (nilai manual, hasil evaluasi SOP di luar sistem).
3. **Begitu `capaian_operasional` diisi**, sistem otomatis: hitung `omset_aktual` = SUM `total_harga` dari `order_cucian` outlet tsb pada periode terkait → bandingkan dengan kedua target → set `memenuhi_target` (boolean) dan `status`.
4. Jika `memenuhi_target = true`: Franchisor input `jumlah_bonus` + `keterangan` → `status → menunggu_pencairan`. Franchisee dapat notifikasi.
5. Franchisor mencairkan: buat `loyalti_pencairan` (`status = diproses`), upload `bukti_transfer` → `loyalti.status → diproses_pencairan`.
6. Franchisee konfirmasi (**bukan Franchisor yang menyatakan sendiri** — pemisahan wewenang ini penting untuk audit):
   - "Diterima" → `loyalti_pencairan.status → selesai`, `loyalti.status → selesai`.
   - "Belum Diterima" → `loyalti_pencairan.status → gagal`, `loyalti.status → menunggu_pencairan` (untuk dicairkan ulang).

> **Catatan:** karena `loyalti_pencairan.status = gagal` bersifat terminal untuk baris itu, pencairan ulang (retry) harus membuat **baris `loyalti_pencairan` baru**, bukan mengubah baris yang gagal — supaya riwayat percobaan pencairan sebelumnya (termasuk `bukti_transfer` yang salah/tidak sampai) tetap tersimpan untuk audit.

---

## 5. MODUL 7 — Dashboard Monitoring (Read-only)

Lima dashboard terpisah, masing-masing dibatasi otomatis sesuai cakupan data aktor (bukan satu dashboard generik). **Tidak ada aksi tulis** dari halaman manapun di modul ini — setiap pesan kondisi kritis berupa tautan (deep link) ke modul terkait, bukan aksi langsung.

| Dashboard | Aktor | Data yang ditampilkan | Kondisi kritis |
|---|---|---|---|
| 7.1 | Franchisor | Ringkasan seluruh outlet real-time: data outlet, agregat omzet dari `order_cucian`, status `loyalti`, jumlah/status `order_cucian`, status `detail_mesin` per outlet. Filter: Outlet, Periode, Kategori. | Mesin bermasalah; loyalti tidak memenuhi target / tertunda pencairan |
| 7.2 | Tim Pengadaan | Ringkasan `purchase_order`, `distribusi_barang`, `permintaan_stok_outlet`, agregat `stok_pusat_bahan_baku` / `stok_pusat_mesin` | Stok pusat menipis; PO/permintaan lama belum divalidasi |
| 7.3 | Supplier | `purchase_order` miliknya, `distribusi_barang` yang belum dikonfirmasi, stok supplier miliknya (dibatasi `supplier_id`) | PO lama belum divalidasi; distribusi lama belum dikonfirmasi Tim Pengadaan |
| 7.4 | Franchisee | Omzet outlet miliknya, status `loyalti`, riwayat bonus (dibatasi `franchise_id`) | Notifikasi bonus loyalti baru menunggu konfirmasi |
| 7.5 | Manajer Outlet | `order_cucian`, `stok_outlet_bahan_baku` (termasuk yang di bawah `stok_minimum`), status `detail_mesin`, `jadwal_service_mesin` (dibatasi outlet via `user_outlet`) | Stok menipis; mesin bermasalah; jadwal service menunggu persetujuan terlalu lama |

**Definisi "kritis" yang perlu disepakati sebagai konstanta konfigurasi** (bukan hardcode):
- "Stok menipis" = `stok_saat_ini < stok_minimum` (level outlet) — level pusat perlu ambang terpisah karena `stok_pusat_bahan_baku` tidak punya kolom minimum di skema saat ini (**gap skema**: pertimbangkan tambah `stok_minimum` juga di `stok_pusat_bahan_baku`).
- "PO/permintaan lama belum divalidasi" dan "jadwal service menunggu terlalu lama" perlu ambang waktu (mis. > 2×24 jam) yang sebaiknya jadi setting, bukan angka tetap di kode.

---

## 6. Kriteria Penerimaan (Acceptance Criteria)

**Modul 5**
- [ ] Order cucian tidak bisa dibuat jika tidak ada `detail_mesin` berstatus `aktif` di outlet terkait.
- [ ] Stok bahan baku outlet berkurang otomatis tepat sejumlah `konsumsi_per_kg × berat` saat order dibuat, dan bertambah kembali saat order dibatalkan.
- [ ] Status mesin kembali ke `aktif` atau berpindah ke `maintenance` sesuai keberadaan jadwal service yang `disetujui` + `menunggu_mesin_bebas`, konsisten baik order selesai maupun dibatalkan.
- [ ] Permintaan stok hanya bisa dihapus outlet selama status `diajukan`.
- [ ] Rollup status permintaan & distribusi outlet terhitung otomatis dari detail, tidak ada input manual di header.
- [ ] Distribusi outlet mendukung multi-batch pengiriman sampai `jumlah_disetujui` terpenuhi penuh.
- [ ] Stok pusat berkurang dan stok outlet bertambah pada event yang sama (konfirmasi penerimaan), bukan pada event pengiriman.
- [ ] Jadwal service otomatis ditolak sistem jika mesin sedang `maintenance`/`nonaktif` saat diajukan.
- [ ] Perubahan status mesin ke `maintenance` tertunda dengan benar jika mesin sedang `digunakan` saat disetujui Franchisor.
- [ ] Jadwal shift staf tidak bisa diedit setelah `berjalan`.

**Modul 6**
- [ ] Franchisee hanya bisa melihat (bukan mengedit) data outlet miliknya.
- [ ] `omset_aktual` dihitung otomatis dari `order_cucian`, tidak diinput manual.
- [ ] Status `loyalti` tidak bisa lompat ke `menunggu_pencairan` jika `memenuhi_target = false`.
- [ ] Konfirmasi penerimaan bonus hanya bisa dilakukan Franchisee, bukan Franchisor.
- [ ] Retry pencairan setelah "Belum Diterima" membuat baris `loyalti_pencairan` baru, riwayat lama tetap ada.

**Modul 7**
- [ ] Setiap dashboard menampilkan data yang dibatasi otomatis sesuai scope aktor (tidak ada kebocoran data lintas outlet/franchise/supplier).
- [ ] Tidak ada elemen aksi tulis di halaman dashboard manapun.
- [ ] Ambang "kritis" (stok minimum, waktu tunggu validasi) dapat diubah lewat konfigurasi, bukan hardcode.

## 7. Di Luar Cakupan
- Detail perhitungan `total_harga` di `order_cucian` (asumsi sudah ada logika harga per kg dari `jenis_layanan`, tidak diubah di sini).
- Definisi lengkap tabel `status` sebagai master lookup (diasumsikan sudah ada, dipakai lintas modul).
- Notifikasi real-time (push/WhatsApp) — pada modul ini disebut sebagai "notifikasi" secara fungsional, mekanisme pengirimannya di luar cakupan PRD.
- Perhitungan `estimasi_selesai` berdasarkan jenis layanan (diasumsikan sudah ada aturan di tabel `jenis_layanan`, tidak didefinisikan ulang di sini).
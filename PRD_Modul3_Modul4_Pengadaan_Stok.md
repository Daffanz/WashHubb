# PRD — Modul 3: Pengadaan Barang dari Supplier & Modul 4: Manajemen Stok Otomatis

**Versi:** 1.0
**Tanggal:** 12 Juli 2026
**Status:** Draft

---

## 0. Ringkasan & Keterhubungan Antar Modul

Dokumen ini mendeskripsikan dua modul yang saling bergantung dalam sistem:

- **Modul 3 — Pengadaan Barang** mengatur *proses transaksi* antara Tim Pengadaan dan Supplier: pembuatan PO, validasi, distribusi, penerimaan, hingga retur.
- **Modul 4 — Manajemen Stok Otomatis** mengatur *efek stok* yang ditimbulkan oleh transaksi di Modul 3: pengurangan stok Supplier saat barang dikirim, dan penambahan stok Pusat saat barang diterima.

Kaidah penghubungnya sederhana:

> **Modul 3 adalah sumber kejadian (event source). Modul 4 adalah efek samping otomatis dari kejadian tersebut — tidak ada input manual ke tabel stok.**

Setiap perubahan status di Modul 3 (`distribusi_barang` → `dikirim`, `penerimaan_barang` dikonfirmasi, `retur_barang` → `selesai`) memicu mutasi stok di Modul 4 secara otomatis melalui sistem (bukan form input stok terpisah), kecuali untuk stok Supplier yang memang boleh diinput manual oleh Supplier sendiri (UC-39/UC-40) sebagai stok awal/tambahan di luar hasil PO.

Peta keterhubungan:

| Kejadian di Modul 3 | Efek di Modul 4 |
|---|---|
| `distribusi_barang` → `dikirim` / `dikirim_sebagian` | `stok_supplier_*` **berkurang**, tercatat di `mutasi_stok_supplier_*` |
| `penerimaan_barang` dikonfirmasi kondisi baik | `stok_pusat_*` **bertambah**, tercatat di `mutasi_stok_pusat_*` |
| `penerimaan_barang` ada barang cacat → `retur_barang` dibuat | Qty cacat **tidak** menambah stok pusat sampai retur selesai |
| `retur_barang` → `selesai` (barang pengganti dikonfirmasi sesuai) | `stok_pusat_*` bertambah untuk qty pengganti |
| Supplier tambah/hapus stok manual (UC-39/40) | `stok_supplier_*` berubah langsung, tanpa melalui PO |

---

# MODUL 3 — PENGADAAN BARANG DARI SUPPLIER

## 1. Ruang Lingkup

Interaksi **Tim Pengadaan ↔ Supplier** untuk pengadaan bahan baku/mesin dari Supplier ke gudang pusat. Distribusi dari gudang pusat ke outlet **di luar cakupan modul ini**.

## 2. Aktor

| Aktor | Peran Utama |
|---|---|
| **Tim Pengadaan** | Membuat PO, mengirim ke Supplier, menerima barang, mengelola retur, memantau riwayat & stok pusat |
| **Supplier** | Memvalidasi PO, mendistribusikan barang, mengelola stok miliknya, mengirim barang pengganti retur |

## 3. Alur Proses End-to-End

```
[1] Tim Pengadaan buat PO (status: diajukan)
        │  isi item bahan baku/mesin
        ▼
[2] Tim Pengadaan kirim PO ke Supplier (status: dikirim)
        │  PO terkunci dari sisi Tim Pengadaan
        ▼
[3] Supplier validasi tiap item
        │  Disetujui / Disetujui Sebagian / Ditolak
        ▼  (agregasi status)
    status PO: disetujui / disetujui_sebagian / ditolak
        │
        ▼ (untuk item yang disetujui)
[4] Supplier distribusikan barang (dikirim / dikirim_sebagian)
        │  → STOK SUPPLIER BERKURANG (Modul 4)
        ▼
[5] Tim Pengadaan terima barang
        ├── Kondisi baik penuh → STOK PUSAT BERTAMBAH (Modul 4), distribusi → diterima
        └── Ada barang cacat  → retur_barang dibuat (menunggu_pengganti),
                                  distribusi → dikirim_sebagian,
                                  stok pusat bertambah HANYA utk barang tidak cacat
                │
                ▼
[6] Proses Retur
    Supplier kirim pengganti (pengganti_dikirim)
        │
        ▼
    Tim Pengadaan konfirmasi
        ├── Sesuai     → STOK PUSAT BERTAMBAH, distribusi → diterima, retur → selesai
        └── Tidak sesuai → retur tetap menunggu_pengganti, notifikasi ke Supplier
        ▼
[7] PO status → selesai (setelah seluruh item diterima/diretur tuntas)
```

## 4. Detail Proses per Tahap

### 4.1 Pembuatan dan Pengisian Detail PO (UC-25, UC-26, UC-27, UC-28)

1. Tim Pengadaan membuat `purchase_order`, memilih **Supplier** dan **jenis_po** (`bahan_baku` / `mesin`). Status awal: **diajukan**.
2. **Filter Supplier otomatis**: daftar Supplier yang tampil difilter berdasarkan `jenis_supplier` yang cocok dengan `jenis_po` — misal `jenis_po = mesin` hanya menampilkan Supplier dengan `jenis_supplier = mesin`.
3. Selama status **diajukan**, Tim Pengadaan dapat:
   - Menambah/mengisi item (`purchase_order_item_bahan_baku` atau `purchase_order_item_mesin`, sesuai `jenis_po`).
   - Menghapus PO beserta seluruh detailnya, **hanya jika belum dikirim ke Supplier**.
4. Begitu status berubah menjadi **dikirim** atau lebih lanjut, PO **tidak dapat lagi dihapus atau diubah** oleh Tim Pengadaan.

**Business rule:** satu baris item per kombinasi `(po_id, bahan_baku_id)` atau `(po_id, mesin_id)` — dijamin unique index di skema.

### 4.2 Pengiriman PO ke Supplier (UC-30)

- Tim Pengadaan klik **"Kirim ke Supplier"**.
- Validasi sistem: PO harus memiliki **minimal satu item**.
- Efek: status → **dikirim**, notifikasi terkirim ke Supplier, kemampuan edit/hapus dari Tim Pengadaan **terkunci**.

### 4.3 Validasi PO oleh Supplier (UC-31)

Supplier memvalidasi **item per item**. Karena PO sudah difilter jenis sejak pembuatan, Supplier hanya melihat PO yang sesuai jenis dagangannya.

Per item, hasil validasi:

| Keputusan | `qty_disetujui` | Field wajib |
|---|---|---|
| Disetujui | = `jumlah` (qty diminta) | — |
| Disetujui Sebagian | < `jumlah`, sesuai kemampuan pasok | `alasan` |
| Ditolak | = 0 | `alasan` |

**Agregasi status PO** dari status seluruh item:

| Kondisi Item | Status PO |
|---|---|
| Semua item Disetujui | `disetujui` |
| Semua item Ditolak | `ditolak` |
| Kombinasi apa pun (campuran) | `disetujui_sebagian` |

Setelah validasi: PO **terkunci**, notifikasi hasil dikirim ke Tim Pengadaan. **Kekurangan pada item `disetujui_sebagian`/`ditolak` tidak otomatis di-carry-over** — ditindaklanjuti lewat PO baru secara manual oleh Tim Pengadaan.

### 4.4 Distribusi Barang oleh Supplier (UC-35, UC-36)

- Untuk item yang disetujui (qty_disetujui > 0), Supplier mencatat pengiriman melalui `distribusi_barang` + detail (`distribusi_barang_detail_bahan_baku` / `_mesin`).
- Status distribusi: **dikirim** (seluruh qty_disetujui terkirim sekaligus) atau **dikirim_sebagian** (dikirim bertahap).
- **Validasi sistem:**
  - Jumlah kirim (`jumlah_kirim`) ≤ jumlah disetujui (`qty_disetujui`) pada item terkait.
  - Sistem **menolak pengiriman baru** apabila masih ada pengiriman sebelumnya (untuk PO yang sama) yang **belum dikonfirmasi diterima** oleh Tim Pengadaan.
- **Efek Modul 4:** begitu `distribusi_barang` berstatus `dikirim`/`dikirim_sebagian`, **stok Supplier langsung berkurang** (lihat Modul 4 §2.1).
- Supplier dapat melihat riwayat distribusinya (filter tanggal/jenis item/tujuan) dan mengunduh PDF.

### 4.5 Penerimaan Barang oleh Tim Pengadaan (UC-34)

Tim Pengadaan mencatat `penerimaan_barang` + detail (`penerimaan_barang_detail_bahan_baku` / `_mesin`), termasuk: kondisi, diskon, ppn, dan `nomor_seri` (khusus mesin).

Dua skenario:

**A. Kondisi baik (seluruh qty_diterima tidak cacat)**
- `stok_pusat_bahan_baku` / `stok_pusat_mesin` **bertambah** sejumlah `qty_diterima`, tercatat di `mutasi_stok_pusat_*`.
- Status `distribusi_barang` → **diterima**.

**B. Ada barang cacat (sebagian atau seluruhnya)**
- Dibuat `retur_barang` + detail (`qty_retur`, `alasan`, `foto_bukti`).
- **Stok pusat hanya bertambah untuk bagian yang tidak cacat** (qty diterima dikurangi qty retur).
- Status `distribusi_barang` → **dikirim_sebagian** (menandakan masih ada bagian yang belum "clear" diterima).
- Status `retur_barang` → **menunggu_pengganti**.

> **Catatan konsistensi status:** "diterima sebagian" pada level fisik direpresentasikan lewat kombinasi status `distribusi_barang = dikirim_sebagian` + adanya `retur_barang` aktif, bukan status distribusi tersendiri bernama "diterima_sebagian". Ini penting dipahami tim dev agar query status tidak salah asumsi.

### 4.6 Proses Retur (UC-37, UC-38)

1. **Supplier input barang pengganti** → status `retur_barang` → **pengganti_dikirim**.
2. **Tim Pengadaan periksa & konfirmasi:**
   - **Sesuai** → `stok_pusat_*` bertambah (qty pengganti), `distribusi_barang` → **diterima**, `retur_barang` → **selesai**.
   - **Tidak sesuai** → Tim Pengadaan menolak; `retur_barang` **tetap** `menunggu_pengganti`, `distribusi_barang` **tetap** `dikirim_sebagian`, notifikasi penolakan dikirim ke Supplier (kembali ke langkah 1).

### 4.7 Riwayat, Monitoring, dan Manajemen Stok Supplier (UC-29, UC-32, UC-33, UC-39, UC-40)

| Use Case | Aktor | Deskripsi |
|---|---|---|
| UC-29 | Tim Pengadaan | Riwayat PO dengan status resmi: `diajukan → dikirim → disetujui/disetujui_sebagian/ditolak → selesai`; filter tanggal/tipe/status; unduh PDF |
| UC-32 | Supplier | Lihat detail stok miliknya sendiri (masuk, keluar, saat ini, riwayat mutasi). Jenis item otomatis dibatasi sesuai `jenis_supplier` login |
| UC-33 | Tim Pengadaan | Lihat detail stok gudang pusat — agregat total per bahan baku/mesin (bukan per outlet) |
| UC-39 | Supplier | Menambah stok miliknya sendiri (di luar hasil PO). Pilihan "jenis item" otomatis dikunci sesuai `jenis_supplier` login |
| UC-40 | Supplier | Menghapus stok miliknya, **hanya jika belum pernah didistribusikan** |

## 5. State Machine Ringkasan

**`purchase_order.status`**
```
diajukan → dikirim → { disetujui | disetujui_sebagian | ditolak } → selesai
```

**`purchase_order_item_*.status`**
```
(default saat dibuat) → { disetujui | disetujui_sebagian | ditolak }
```

**`distribusi_barang.status`**
```
dikirim ⇄ dikirim_sebagian → diterima
```

**`retur_barang.status`**
```
menunggu_pengganti → pengganti_dikirim → { selesai | menunggu_pengganti (ditolak, ulangi) }
```

## 6. Business Rules Kunci — Modul 3

1. Daftar Supplier pada form PO **wajib** difilter oleh `jenis_supplier = jenis_po` — dan sebaliknya, Supplier hanya melihat PO yang jenisnya sesuai dagangannya.
2. PO hanya bisa dihapus saat status **diajukan**.
3. PO tidak bisa dikirim tanpa minimal 1 item.
4. Setelah **dikirim**, PO immutable dari sisi Tim Pengadaan.
5. Validasi item bersifat granular (per item), status PO adalah **hasil agregasi**, bukan input langsung.
6. Kekurangan qty akibat `disetujui_sebagian`/`ditolak` **tidak otomatis** menjadi PO baru — perlu tindakan manual.
7. Distribusi baru **diblokir** jika masih ada distribusi sebelumnya (PO yang sama) yang belum dikonfirmasi diterima.
8. Barang cacat **tidak pernah** menambah stok pusat langsung — harus melalui alur retur yang selesai.
9. Penolakan retur oleh Tim Pengadaan **mengembalikan** proses ke Supplier, tidak ada state "gagal permanen" — retur harus tuntas.

## 7. Skema Database — Modul 3 (ringkasan referensi)

| Tabel | Fungsi |
|---|---|
| `purchase_order` | Header PO: supplier, pembuat, jenis_po, total_nilai, status |
| `purchase_order_item_bahan_baku` / `_mesin` | Baris item PO per jenis, termasuk qty diminta & disetujui |
| `distribusi_barang` | Header pengiriman dari Supplier, per PO |
| `distribusi_barang_detail_bahan_baku` / `_mesin` | Rincian qty kirim per item PO |
| `penerimaan_barang` | Header penerimaan oleh Tim Pengadaan, termasuk subtotal/diskon/ppn/total_bayar |
| `penerimaan_barang_detail_bahan_baku` / `_mesin` | Rincian qty diterima & kondisi per detail distribusi (mesin: `nomor_seri`) |
| `retur_barang` | Header retur, terhubung ke `penerimaan_barang` |
| `retur_barang_detail_bahan_baku` / `_mesin` | Rincian qty retur, alasan, foto bukti, qty & nomor seri pengganti |
| `supplier` | Master Supplier, termasuk `jenis_supplier` (guard filter) |

---

# MODUL 4 — MANAJEMEN STOK OTOMATIS

## 1. Ruang Lingkup

Mencakup **stok gudang pusat** (`stok_pusat_bahan_baku`, `stok_pusat_mesin`) dan **stok Supplier** (`stok_supplier_bahan_baku`, `stok_supplier_mesin`). **Tidak mencakup stok outlet** — tabel tersebut belum ada di skema dan akan menjadi modul tersendiri.

Modul ini **tidak memiliki UI transaksi sendiri untuk stok pusat** — seluruh pergerakan stok pusat murni merupakan **efek otomatis** dari kejadian di Modul 3. Stok Supplier sedikit berbeda: bisa terpengaruh dari Modul 3 (distribusi) **maupun** diinput manual oleh Supplier sendiri (UC-39/40).

## 2. Prinsip Utama & Trigger Mutasi

### 2.1 Stok Supplier berkurang saat distribusi

- Trigger: `distribusi_barang` berubah menjadi **dikirim** atau **dikirim_sebagian**.
- Efek: `stok_supplier_bahan_baku.stok_saat_ini` atau `stok_supplier_mesin.stok_saat_ini` **berkurang** sejumlah `jumlah_kirim` pada masing-masing detail.
- Tercatat sebagai baris baru di `mutasi_stok_supplier_bahan_baku` / `_mesin` (`jenis_mutasi = keluar`, `jumlah`, `tanggal`).

### 2.2 Stok Pusat bertambah saat penerimaan dikonfirmasi

- Trigger: `penerimaan_barang` dikonfirmasi dengan `kondisi = baik` (mengacu ke `penerimaan_barang_detail_*`).
- Efek: `stok_pusat_bahan_baku.stok_saat_ini` atau `stok_pusat_mesin.stok_saat_ini` **bertambah** sejumlah `qty_diterima` (dikurangi `qty_retur` bila ada barang cacat pada baris yang sama).
- Tercatat di `mutasi_stok_pusat_*` (`stok_masuk`).

### 2.3 Barang retur tidak menambah stok pusat sampai tuntas

- Selama `retur_barang.status ∈ {menunggu_pengganti, pengganti_dikirim}`, qty yang diretur **tidak dihitung** sebagai penambahan stok pusat.
- Stok pusat baru bertambah untuk qty pengganti **setelah** `retur_barang.status = selesai` (Tim Pengadaan mengonfirmasi barang pengganti sesuai).
- Ini mencegah **double counting** maupun **stok fiktif** dari barang yang secara fisik belum benar-benar diterima dalam kondisi baik.

### 2.4 `detail_mesin` sebagai fondasi modul lanjutan (belum aktif)

- Tabel `detail_mesin` (unit fisik mesin: `nomor_seri`, `outlet_id`, `status_id`: aktif/digunakan/maintenance/nonaktif) **disiapkan di skema** tapi **belum digunakan secara aktif** dalam Modul 1–4.
- Fokus Modul 3/4 saat ini **berhenti di level `stok_pusat_mesin`** (agregat jumlah mesin di gudang pusat, bukan unit per nomor seri per outlet).
- Alokasi mesin ke outlet (yang akan mengisi `detail_mesin.outlet_id`) adalah **cakupan modul berikutnya**, di luar dokumen ini.

### 2.5 Guard `jenis_supplier` sebagai validasi level aplikasi

- `jenis_supplier` pada tabel `supplier` berfungsi sebagai **gerbang validasi**, memastikan:
  - Baris yang masuk ke `stok_supplier_bahan_baku` hanya berasal dari Supplier `jenis_supplier = bahan_baku`.
  - Baris yang masuk ke `stok_supplier_mesin` hanya dari Supplier `jenis_supplier = mesin`.
- **Wajib diterapkan di layer Controller/Form Request** (bukan hanya UI/frontend), supaya validasi tetap berlaku meski ada request langsung ke API — mencegah manipulasi via API call yang melewati form.

## 3. Ringkasan Trigger → Efek Stok

| # | Trigger (Modul 3) | Tabel Stok Terpengaruh | Arah | Tabel Mutasi |
|---|---|---|---|---|
| 1 | `distribusi_barang → dikirim/dikirim_sebagian` | `stok_supplier_bahan_baku` / `_mesin` | Berkurang | `mutasi_stok_supplier_*` |
| 2 | `penerimaan_barang` dikonfirmasi (kondisi baik) | `stok_pusat_bahan_baku` / `_mesin` | Bertambah | `mutasi_stok_pusat_*` |
| 3 | Barang cacat teridentifikasi saat penerimaan | *(tidak ada penambahan stok pusat untuk qty cacat)* | — | — |
| 4 | `retur_barang → selesai` (pengganti dikonfirmasi sesuai) | `stok_pusat_bahan_baku` / `_mesin` | Bertambah (qty pengganti) | `mutasi_stok_pusat_*` |
| 5 | Supplier tambah stok manual (UC-39) | `stok_supplier_bahan_baku` / `_mesin` | Bertambah | `mutasi_stok_supplier_*` |
| 6 | Supplier hapus stok (UC-40, belum pernah didistribusi) | `stok_supplier_bahan_baku` / `_mesin` | Baris dihapus | *(tidak perlu mutasi, karena belum pernah keluar)* |

## 4. Business Rules Kunci — Modul 4

1. **Tidak ada input manual ke `stok_pusat_*`** — nilai `stok_saat_ini` murni hasil agregasi mutasi otomatis dari Modul 3.
2. Setiap perubahan `stok_saat_ini` **wajib** disertai baris baru di tabel mutasi terkait (audit trail), tidak boleh update langsung tanpa jejak.
3. `stok_supplier_*` boleh berkurang dari distribusi (otomatis) **atau** bertambah/berkurang dari input manual Supplier (UC-39/40) — dua sumber yang sah, keduanya harus tercatat di `mutasi_stok_supplier_*`.
4. Penghapusan stok Supplier (UC-40) hanya diizinkan jika baris tersebut **belum pernah muncul** di `mutasi_stok_supplier_*` dengan `jenis_mutasi = keluar` (belum pernah didistribusikan).
5. Validasi `jenis_supplier` sebagai guard **berlaku di semua entry point** (form UI dan API), bukan hanya UI.
6. Perhitungan qty stok pusat pada kasus penerimaan sebagian-cacat = `qty_diterima − qty_retur` (dari baris `penerimaan_barang_detail_*` yang sama), dieksekusi dalam satu transaksi database dengan pembuatan `retur_barang`.
7. Stok outlet **eksplisit di luar cakupan** — jangan membuat asumsi/skema tambahan untuk itu di modul ini.

## 5. Skema Database — Modul 4 (ringkasan referensi)

| Tabel | Fungsi |
|---|---|
| `stok_pusat_bahan_baku` | Agregat stok bahan baku di gudang pusat (`stok_masuk`, `stok_keluar`, `stok_saat_ini`) |
| `stok_pusat_mesin` | Agregat stok mesin di gudang pusat (struktur setara `stok_pusat_bahan_baku`) |
| `stok_supplier_bahan_baku` | Stok bahan baku milik tiap Supplier (unique per `supplier_id + bahan_baku_id`) |
| `stok_supplier_mesin` | Stok mesin milik tiap Supplier (unique per `supplier_id + mesin_id`) |
| `mutasi_stok_supplier_bahan_baku` / `_mesin` | Log setiap perubahan stok Supplier (jenis_mutasi, jumlah, tanggal) |
| `mutasi_stok_pusat_bahan_baku` / `_mesin` | Log setiap perubahan stok pusat *(catatan: tabel mutasi stok pusat perlu ditambahkan ke DBML jika belum ada — lihat §7 Open Items)* |
| `detail_mesin` | Unit fisik mesin (fondasi modul alokasi outlet, belum aktif digunakan) |

---

# KETERHUBUNGAN MODUL 3 ↔ MODUL 4 (Integration Contract)

Agar dua tim (jika dikerjakan terpisah) tetap sinkron, berikut kontrak integrasinya:

| Event dari Modul 3 | Payload minimal yang dibutuhkan Modul 4 | Aksi Modul 4 |
|---|---|---|
| Distribusi dikirim | `po_id`, daftar `(po_item_id, jumlah_kirim)`, `jenis_po` | Kurangi `stok_supplier_*` sesuai `supplier_id` dari PO, catat mutasi `keluar` |
| Penerimaan dikonfirmasi baik | `distribusi_barang_detail_id`, `qty_diterima`, `kondisi` | Tambah `stok_pusat_*`, catat mutasi `masuk` |
| Penerimaan dengan barang cacat | `qty_diterima`, `qty_retur`, `alasan`, `foto_bukti` | Tambah `stok_pusat_*` hanya untuk `qty_diterima − qty_retur`; **tidak** proses qty_retur |
| Retur selesai (pengganti sesuai) | `retur_barang_detail_id`, `qty_pengganti`, (mesin: `nomor_seri_pengganti`) | Tambah `stok_pusat_*` sejumlah `qty_pengganti`, catat mutasi `masuk` |
| Supplier tambah/hapus stok manual | `supplier_id`, `jenis_item`, `jumlah` | Guard `jenis_supplier`, lalu update `stok_supplier_*` langsung (bypass Modul 3) |

**Prinsip transaksional:** setiap event di atas **wajib dieksekusi dalam satu database transaction** bersama perubahan status di Modul 3 (misalnya: update `distribusi_barang.status` dan pengurangan `stok_supplier_*` harus atomic) — untuk mencegah status berubah tapi stok tidak ter-update, atau sebaliknya.

---

## Open Items / Catatan untuk Tim Dev

1. **Tabel `mutasi_stok_pusat_bahan_baku` / `_mesin`** disebutkan sebagai target pencatatan mutasi di deskripsi Modul 4, namun belum terlihat definisinya secara eksplisit di potongan DBML yang tersedia (skema `stok_pusat_bahan_baku` di dokumen terpotong pada bagian akhir). Perlu dipastikan/ditambahkan strukturnya (kemungkinan besar mirror dari `mutasi_stok_supplier_*`: `stok_pusat_id`, `jenis_mutasi`, `jumlah`, `tanggal`, `created_at`).
2. **Penomoran status "diterima sebagian"** tidak punya kode `status` tersendiri di tabel referensi (`distribusi_barang` hanya punya `dikirim`, `dikirim_sebagian`, `diterima`) — dikonfirmasi bahwa representasinya adalah kombinasi `dikirim_sebagian` + `retur_barang` aktif. Pastikan tim frontend menampilkan label gabungan ini dengan jelas ke pengguna agar tidak membingungkan (misal label UI: "Diterima Sebagian — Menunggu Retur").
3. **PO status `selesai`** — trigger pastinya (semua item baik disetujui/ditolak sudah melalui distribusi+penerimaan+retur tuntas) belum didetailkan eksplisit di dokumen sumber; disarankan didefinisikan sebagai: seluruh `distribusi_barang` terkait PO berstatus `diterima` DAN seluruh `retur_barang` terkait berstatus `selesai` (atau tidak ada retur aktif).
4. **Modul stok outlet** dan **alokasi `detail_mesin` ke outlet** adalah kandidat Modul 5, memanfaatkan fondasi `detail_mesin` yang sudah disiapkan di skema ini.


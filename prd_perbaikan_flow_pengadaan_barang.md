# PRD: Perbaikan Alur Pengadaan Barang (PO → Distribusi → Retur → Mutasi → Stok)

## 1. Latar Belakang
Alur pengadaan barang saat ini (PO, Distribusi, Retur, Mutasi) belum terhubung otomatis satu sama lain. Status di tiap modul masih bisa diubah manual tanpa aturan yang jelas, sehingga tidak ada satu sumber kebenaran (single source of truth) untuk status pengadaan. PRD ini mendefinisikan ulang alur end-to-end dari PO dibuat sampai barang masuk stok, dengan aturan: **setiap status hanya berubah otomatis mengikuti aksi di tahap sebelumnya**, bukan diedit manual di banyak tempat.

## 2. Tujuan
- Menghilangkan input status manual yang tumpang tindih antar modul (contoh: tombol "Kirim" di PO).
- Membuat status PO selalu mencerminkan kondisi nyata di Distribusi/Retur secara otomatis.
- Memberi tim pengadaan visibilitas alasan (catatan) saat PO ditolak atau dikirim sebagian.
- Menyederhanakan Mutasi dengan menarik data langsung dari PO, bukan input manual ulang.
- Memastikan stok bertambah hanya lewat satu jalur resmi: Mutasi jenis masuk yang mereferensikan PO.

## 3. Aktor & Tanggung Jawab

| Aktor | Tanggung jawab dalam alur ini |
|---|---|
| **Tim Pengadaan** | Membuat PO, menandai barang diterima & mengecek kondisi fisik, membuat Retur, memverifikasi barang pengganti, membuat Mutasi masuk |
| **Supplier** | Membuka Distribusi, mengecek stok, memutuskan kirim penuh/sebagian/tolak, mengirim barang pengganti untuk retur |

> **Keputusan desain:** Status "barang diterima" ditandai oleh **tim pengadaan/gudang**, bukan supplier — karena merekalah pihak yang secara fisik menerima dan bisa memverifikasi kondisi barang. Tanggung jawab supplier berhenti setelah mereka menandai pengiriman (dikirim / dikirim sebagian / ditolak) di Distribusi.

## 4. Modul & Status Enum

### 4.1 Purchase Order (PO)
```
diajukan
  → ditolak                (dari Distribusi: stok supplier kosong)
  → dikirim                (dari Distribusi: stok supplier penuh)
  → dikirim_sebagian       (dari Distribusi: stok supplier sebagian)

dikirim / dikirim_sebagian
  → diterima               (tim pengadaan menandai barang sampai)

diterima
  → selesai                (tim pengadaan klik "Disetujui", barang lengkap & normal)
  → diterima_sebagian      (barang yang benar-benar diterima < yang dikirim/dipesan)

diterima_sebagian
  → selesai                (otomatis, saat Retur terkait berstatus selesai)
```

**Field tambahan di PO:**
| Field | Tipe | Keterangan |
|---|---|---|
| `catatan_status` | text | Diisi supplier saat menolak atau kirim sebagian (alasan) |
| `qty_diterima` | per item bahan baku | Diisi tim pengadaan saat pengecekan fisik, dibandingkan dengan qty PO/qty dikirim |

**Perubahan UI:** hapus tombol "Kirim" manual di halaman PO — status PO tidak bisa diubah manual sama sekali, murni hasil trigger dari Distribusi/Retur.

### 4.2 Distribusi
```
menunggu
  → ditolak            (wajib isi catatan alasan)
  → dikirim
  → dikirim_sebagian   (wajib isi qty per item + catatan)

dikirim / dikirim_sebagian
  → diterima           (ditandai oleh tim pengadaan saat barang sampai)
```
Distribusi adalah **sumber kebenaran** untuk status pengiriman. Setiap perubahan status Distribusi men-trigger update otomatis ke status PO terkait.

### 4.3 Retur
Hanya dibuat dari PO berstatus `diterima_sebagian`.
```
(dibuat)              → menunggu_pengganti
menunggu_pengganti    → kirim_pengganti     (diubah oleh supplier saat barang pengganti dikirim)
kirim_pengganti       → selesai             (tim pengadaan verifikasi fisik di luar sistem, lalu klik selesai)
```
Saat Retur `selesai` → PO terkait **otomatis** ikut menjadi `selesai`.

**Fitur pencarian PO di Retur:** saat tim pengadaan mencari PO, sistem otomatis menampilkan:
- Qty yang sudah diterima (`qty_diterima`)
- Qty yang masih kurang (selisih dari qty dikirim/qty PO)

### 4.4 Mutasi
```
jenis: masuk
  → pilih PO (hanya PO berstatus selesai)
  → item bahan baku/mesin & qty otomatis terisi dari data PO
  → simpan → stok bertambah otomatis
```
**Hapus jenis mutasi:**
- `penyesuaian` — tidak relevan lagi karena semua perubahan stok masuk harus lewat referensi PO.
- `po_disetujui` — redundan, karena klik "Disetujui" di PO sudah otomatis mengubah status PO jadi `selesai`; mutasi masuk dibuat terpisah setelahnya dengan mereferensikan PO yang sama.

### 4.5 Stok
Stok **tidak punya jalur input manual**. Stok hanya berubah sebagai efek otomatis dari Mutasi (masuk = referensi PO, keluar = sesuai jenis mutasi lain yang sudah ada di luar cakupan PRD ini).

## 5. Alur End-to-End (Step by Step)

1. Tim pengadaan membuat PO ke Supplier A → status PO = `diajukan`.
2. Supplier A membuka Distribusi, memilih PO yang `diajukan` ke dirinya, lalu cek stok bahan baku:
   - **Stok kosong** → klik Tolak, isi catatan alasan → Distribusi = `ditolak` → PO otomatis = `ditolak` (catatan tampil di PO).
   - **Stok penuh** → klik Kirim → Distribusi = `dikirim` → PO otomatis = `dikirim`.
   - **Stok sebagian** → klik "Kirim Sebagian", isi qty per item + catatan → Distribusi = `dikirim_sebagian` → PO otomatis = `dikirim_sebagian`.
3. Saat barang fisik sampai, **tim pengadaan** menandai Distribusi = `diterima` → PO ikut `diterima`.
4. Tim pengadaan cek fisik barang & input `qty_diterima` per item:
   - **Barang lengkap & normal** → klik "Disetujui" → PO = `selesai`.
   - **Barang kurang dari yang dikirim/dipesan** → PO = `diterima_sebagian`.
5. Untuk PO `diterima_sebagian`, tim pengadaan buka Retur:
   - Cari PO → sistem otomatis menampilkan qty diterima vs qty kurang.
   - Klik "Retur" → status Retur = `menunggu_pengganti`.
6. Supplier mengirim barang pengganti → ubah status Retur = `kirim_pengganti`.
7. Tim pengadaan verifikasi fisik barang pengganti (di luar sistem) → klik "Selesai" di Retur → Retur = `selesai` → PO otomatis = `selesai`.
8. Setelah PO `selesai` (baik langsung di langkah 4 atau via Retur di langkah 7), tim pengadaan membuat Mutasi jenis `masuk`, pilih PO terkait → item & qty otomatis terisi dari PO → simpan → stok bertambah otomatis.

## 6. Ringkasan Perubahan Sistem

| Area | Perubahan |
|---|---|
| PO | Hapus tombol "Kirim" manual |
| PO | Tambah status `diterima`, `diterima_sebagian` |
| PO | Tambah field `catatan_status` dan `qty_diterima` per item |
| PO | Semua transisi status PO terjadi otomatis dari trigger Distribusi/Retur, tidak ada input manual |
| Distribusi | Tambah aksi Tolak (wajib catatan), Kirim, Kirim Sebagian (wajib qty + catatan) |
| Distribusi | Tambah aksi tandai "diterima" oleh tim pengadaan |
| Distribusi | Trigger auto-update status PO setiap kali status Distribusi berubah |
| Retur | Hanya bisa dibuat dari PO berstatus `diterima_sebagian` |
| Retur | Pencarian PO otomatis menampilkan qty diterima & qty kurang |
| Retur | Tambah status `menunggu_pengganti`, `kirim_pengganti`, `selesai` |
| Retur | Trigger auto-update PO jadi `selesai` saat Retur `selesai` |
| Mutasi | Hapus jenis `penyesuaian` dan `po_disetujui` |
| Mutasi | Jenis `masuk` wajib pilih referensi PO (hanya PO berstatus `selesai`), item & qty auto-fill |
| Stok | Tidak ada input manual; hanya berubah via Mutasi |

## 7. Kriteria Penerimaan (Acceptance Criteria)
- [ ] PO tidak bisa diubah status secara manual di UI — semua transisi berasal dari Distribusi/Retur.
- [ ] Distribusi Tolak dan Kirim Sebagian tidak bisa disimpan tanpa catatan.
- [ ] Kirim Sebagian di Distribusi wajib isi qty per item, tersimpan dan tampil di PO.
- [ ] Status PO otomatis berubah tepat setelah status Distribusi disimpan (tanpa refresh manual/job terpisah yang delay).
- [ ] Retur hanya muncul sebagai opsi untuk PO berstatus `diterima_sebagian`.
- [ ] Pencarian PO di Retur menampilkan qty diterima dan qty kurang secara otomatis dan akurat.
- [ ] Retur `selesai` memicu PO otomatis jadi `selesai` dalam transaksi yang sama (atomic, tidak ada race condition).
- [ ] Jenis mutasi `penyesuaian` dan `po_disetujui` sudah tidak muncul lagi di dropdown/pilihan jenis mutasi.
- [ ] Mutasi jenis `masuk` dengan referensi PO otomatis mengisi item & qty sesuai data PO, dan stok bertambah tepat sejumlah itu saat disimpan.
- [ ] Mutasi jenis `masuk` hanya bisa mereferensikan PO yang berstatus `selesai` (mencegah stok masuk sebelum barang benar-benar clear).

## 8. Di Luar Cakupan (Out of Scope)
- Jenis mutasi selain `masuk` yang mereferensikan PO (mis. mutasi keluar, transfer antar cabang) tidak dibahas di PRD ini.
- Approval flow internal tim pengadaan (multi-level approval sebelum PO diajukan ke supplier) tidak dibahas di sini.
- Notifikasi (WhatsApp/email) ke masing-masing pihak di tiap perubahan status disebut sebagai kebutuhan lanjutan, bukan bagian wajib PRD ini.

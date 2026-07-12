ID	Konteks	Kode
1	akun	aktif
2	akun	nonaktif
3	supplier	aktif
4	supplier	nonaktif
5	stok_supplier	aktif
6	stok_supplier	nonaktif
7	bahan_baku	aktif
8	bahan_baku	nonaktif
9	jenis_layanan	aktif
10	jenis_layanan	nonaktif
11	mesin	aktif
12	mesin	nonaktif
13	purchase_order	diajukan
14	purchase_order	disetujui
15	purchase_order	disetujui_sebagian
16	purchase_order	ditolak
17	purchase_order	dikirim
18	Purchase_order	selesai
19	purchase_order_item	disetujui
20	purchase_order_item	disetujui_sebagian
21	purchase_order_item	ditolak
22	distribusi_barang	dikirim
23	distribusi_barang	dikirim_sebagian
24	distribusi_barang	diterima
25	retur_barang	menunggu_pengganti
26	retur_barang	pengganti_dikirim
27	retur_barang	selesai
28	stok_pusat	aktif
29	stok_pusat	nonaktif
30	detail_mesin	aktif
31	detail_mesin	digunakan
32	detail_mesin	maintenance
33	detail_mesin	nonaktif
34	outlet	aktif
35	outlet	nonaktif
Jenis supplier : supplier bahan baku dan supplier mesin



// ============================================
// ENUM
// ============================================
Enum jenis_po_enum {
  bahan_baku
  mesin
}

Enum jenis_supplier_enum {
  bahan_baku
  mesin
}

// ============================================
// TABEL STATUS
// ============================================
Table status {
  id bigint [pk, increment]
  konteks varchar
  kode varchar
  label varchar

  indexes {
    (konteks, kode) [unique]
  }
}

// ============================================
// TABEL OUTLET
// ============================================
Table outlet {
  id bigint [pk, increment]
  nama varchar
  kode_outlet varchar [unique]
  alamat text
  franchise_id bigint [ref: > franchise.id]
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
}

// ============================================
// MODUL 1
// ============================================
Table roles {
  id bigint [pk, increment]
  kode varchar [unique]
  label varchar
}

Table permissions {
  id bigint [pk, increment]
  kode varchar [unique]
  nama varchar
  modul varchar
  created_at datetime
  updated_at datetime
}

Table role_has_permission {
  id bigint [pk, increment]
  role_id bigint [ref: > roles.id]
  permission_id bigint [ref: > permissions.id]
  created_at datetime
  indexes {
    (role_id, permission_id) [unique]
  }
}

Table users {
  id bigint [pk, increment]
  nama varchar
  email varchar [unique]
  password varchar
  no_telp varchar
  role_id bigint [ref: > roles.id]
  status_id bigint [ref: > status.id]
  wajib_ganti_password boolean
  created_at datetime
  updated_at datetime
}

Table user_outlet {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id]
  outlet_id bigint [ref: > outlet.id]
  created_at datetime
  indexes {
    (user_id, outlet_id) [unique]
  }
}

Table supplier {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id, unique]
  jenis_supplier jenis_supplier_enum
  alamat text
  katalog_produk text
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
}

Table franchise {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id, unique]
  created_at datetime
  updated_at datetime
}

Table manajer_operasional {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id, unique]
  created_at datetime
  updated_at datetime
}

Table stok_supplier_bahan_baku {
  id bigint [pk, increment]
  supplier_id bigint [ref: > supplier.id]
  bahan_baku_id bigint [ref: > bahan_baku.id]
  stok_saat_ini decimal
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
  indexes {
    (supplier_id, bahan_baku_id) [unique]
  }
}

Table mutasi_stok_supplier_bahan_baku {
  id bigint [pk, increment]
  stok_supplier_bahan_baku_id bigint [ref: > stok_supplier_bahan_baku.id]
  jenis_mutasi varchar
  jumlah decimal
  tanggal datetime
  created_at datetime
}

Table stok_supplier_mesin {
  id bigint [pk, increment]
  supplier_id bigint [ref: > supplier.id]
  mesin_id bigint [ref: > mesin.id]
  stok_saat_ini int
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
  indexes {
    (supplier_id, mesin_id) [unique]
  }
}

Table mutasi_stok_supplier_mesin {
  id bigint [pk, increment]
  stok_supplier_mesin_id bigint [ref: > stok_supplier_mesin.id]
  jenis_mutasi varchar
  jumlah int
  tanggal datetime
  created_at datetime
}

// ============================================
// MODUL 2 — DATA MASTER
// ============================================
Table kategori_bahan_baku {
  id bigint [pk, increment]
  nama varchar [unique]
  created_at datetime
  updated_at datetime
}

Table bahan_baku {
  id bigint [pk, increment]
  nama varchar
  kategori_id bigint [ref: > kategori_bahan_baku.id]
  satuan varchar
  harga_standar decimal
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
  indexes {
    (nama, kategori_id) [unique]
  }
}

Table jenis_layanan {
  id bigint [pk, increment]
  nama varchar [unique]
  harga_standar_per_kg decimal
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
}

Table jenis_layanan_bahan_baku {
  id bigint [pk, increment]
  jenis_layanan_id bigint [ref: > jenis_layanan.id]
  bahan_baku_id bigint [ref: > bahan_baku.id]
  konsumsi_per_kg decimal
  created_at datetime
  updated_at datetime
  indexes {
    (jenis_layanan_id, bahan_baku_id) [unique]
  }
}

// ============================================
// MESIN (MASTER MODEL/TIPE MESIN)
// ============================================
Table mesin {
  id bigint [pk, increment]
  nama varchar
  kode_mesin varchar [unique]
  merk varchar
  tipe varchar
  kapasitas int
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
  indexes {
    nama [unique]
  }
}

// ============================================
// MODUL 3 — PENGADAAN BARANG
// ============================================
Table purchase_order {
  id bigint [pk, increment]
  nomor_po varchar [unique]
  supplier_id bigint [ref: > supplier.id]
  dibuat_oleh_id bigint [ref: > users.id]
  jenis_po jenis_po_enum
  total_nilai decimal
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
}

Table purchase_order_item_bahan_baku {
  id bigint [pk, increment]
  po_id bigint [ref: > purchase_order.id]
  bahan_baku_id bigint [ref: > bahan_baku.id]
  jumlah decimal
  harga_satuan decimal
  qty_disetujui decimal
  status_id bigint [ref: > status.id]
  alasan text
  created_at datetime
  updated_at datetime
  indexes {
    (po_id, bahan_baku_id) [unique]
  }
}

Table purchase_order_item_mesin {
  id bigint [pk, increment]
  po_id bigint [ref: > purchase_order.id]
  mesin_id bigint [ref: > mesin.id]
  jumlah int
  harga_satuan decimal
  qty_disetujui int
  status_id bigint [ref: > status.id]
  alasan text
  created_at datetime
  updated_at datetime
  indexes {
    (po_id, mesin_id) [unique]
  }
}

Table distribusi_barang {
  id bigint [pk, increment]
  po_id bigint [ref: > purchase_order.id]
  nomor_distribusi varchar [unique]
  tanggal_kirim date
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
}

Table distribusi_barang_detail_bahan_baku {
  id bigint [pk, increment]
  distribusi_barang_id bigint [ref: > distribusi_barang.id]
  po_item_id bigint [ref: > purchase_order_item_bahan_baku.id]
  jumlah_kirim decimal
  created_at datetime
  updated_at datetime
}

Table distribusi_barang_detail_mesin {
  id bigint [pk, increment]
  distribusi_barang_id bigint [ref: > distribusi_barang.id]
  po_item_id bigint [ref: > purchase_order_item_mesin.id]
  jumlah_kirim int
  created_at datetime
  updated_at datetime
}

Table penerimaan_barang {
  id bigint [pk, increment]
  distribusi_barang_id bigint [ref: > distribusi_barang.id]
  nomor_penerimaan varchar [unique]
  tanggal_terima date
  subtotal decimal
  diskon decimal
  ppn decimal
  total_bayar decimal
  created_at datetime
  updated_at datetime
}

Table penerimaan_barang_detail_bahan_baku {
  id bigint [pk, increment]
  penerimaan_barang_id bigint [ref: > penerimaan_barang.id]
  distribusi_barang_detail_bahan_baku_id bigint [ref: > distribusi_barang_detail_bahan_baku.id]
  qty_diterima decimal
  kondisi varchar
  created_at datetime
  updated_at datetime
}

Table penerimaan_barang_detail_mesin {
  id bigint [pk, increment]
  penerimaan_barang_id bigint [ref: > penerimaan_barang.id]
  distribusi_barang_detail_mesin_id bigint [ref: > distribusi_barang_detail_mesin.id]
  nomor_seri varchar
  qty_diterima int
  kondisi varchar
  created_at datetime
  updated_at datetime
}

// ============================================
// RETUR BARANG
// ============================================
Table retur_barang {
  id bigint [pk, increment]
  penerimaan_barang_id bigint [ref: > penerimaan_barang.id]
  tanggal_retur datetime
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
}

Table retur_barang_detail_bahan_baku {
  id bigint [pk, increment]
  retur_barang_id bigint [ref: > retur_barang.id]
  penerimaan_barang_detail_bahan_baku_id bigint [ref: > penerimaan_barang_detail_bahan_baku.id]
  qty_retur decimal
  alasan text
  foto_bukti varchar
  qty_pengganti decimal
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
}

Table retur_barang_detail_mesin {
  id bigint [pk, increment]
  retur_barang_id bigint [ref: > retur_barang.id]
  penerimaan_barang_detail_mesin_id bigint [ref: > penerimaan_barang_detail_mesin.id]
  qty_retur int
  alasan text
  foto_bukti varchar
  qty_pengganti int
  nomor_seri_pengganti varchar [ref: > detail_mesin.nomor_seri]
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
}

// ============================================
// DETAIL MESIN (UNIT FISIK DI OUTLET)
// ============================================
Table detail_mesin {
  id bigint [pk, increment]
  mesin_id bigint [ref: > mesin.id]
  nomor_seri varchar [unique]
  outlet_id bigint [ref: > outlet.id]
  tanggal_terima_pusat date
  status_id bigint [ref: > status.id]
  created_at datetime
  updated_at datetime
}

// ============================================
// MODUL 4 — MANAJEMEN STOK
// ============================================
Table stok_pusat_bahan_baku {
  id bigint [pk, increment]
  bahan_baku_id bigint [ref: > bahan_baku.id]
  stok_masuk decimal
  stok_keluar decimal
  stok_saat_ini decimal
  created_at datetime
  updated_at datetime
  indexes {
    bahan_baku_id [unique]
  }
}

Table mutasi_stok_pusat_bahan_baku {
  id bigint [pk, increment]
  stok_pusat_bahan_baku_id bigint [ref: > stok_pusat_bahan_baku.id]
  jenis_mutasi varchar
  jumlah decimal
  tanggal datetime
  penerimaan_barang_detail_bahan_baku_id bigint [ref: > penerimaan_barang_detail_bahan_baku.id]
  created_at datetime
}

Table stok_pusat_mesin {
  id bigint [pk, increment]
  mesin_id bigint [ref: > mesin.id]
  stok_masuk int
  stok_keluar int
  stok_saat_ini int
  created_at datetime
  updated_at datetime
  indexes {
    mesin_id [unique]
  }
}

Table mutasi_stok_pusat_mesin {
  id bigint [pk, increment]
  stok_pusat_mesin_id bigint [ref: > stok_pusat_mesin.id]
  jenis_mutasi varchar
  jumlah int
  tanggal datetime
  penerimaan_barang_detail_mesin_id bigint [ref: > penerimaan_barang_detail_mesin.id]
  created_at datetime
}

FULL ALUR BISNIS (REVISI)
MODUL 1 — AKUN DAN DATA SUPPLIER
Modul ini berkaitan dengan pengelolaan akun dan data identitas Supplier sebagai aktor eksternal, sekaligus penentuan jenis dagangan Supplier (bahan baku atau mesin).
1.	Tim IT membuat akun pengguna (users) untuk calon mitra Supplier melalui menu Manajemen User (UC-01), dengan role_id sesuai role Supplier dan status awal aktif.
2.	Tim Pengadaan menginput data profil Supplier melalui menu Supplier (UC-22/23/24), terhubung satu-ke-satu (user_id unique) dengan akun yang sudah dibuat Tim IT. Pada tahap ini, Tim Pengadaan wajib menentukan jenis_supplier — apakah Supplier tersebut berjualan Bahan Baku atau Mesin. Field ini bersifat final saat pembuatan data (tidak disarankan diubah setelah Supplier punya riwayat stok/PO, untuk menghindari data yang tidak konsisten).
3.	jenis_supplier ini menjadi penanda utama yang dipakai di seluruh Modul 3 untuk: 
o	Membatasi jenis stok apa saja yang boleh diinput Supplier (UC-39/40).
o	Memfilter daftar Supplier saat Tim Pengadaan membuat PO sesuai jenis_po (UC-25).
o	Memfilter daftar PO yang muncul saat Supplier melakukan validasi (UC-31) dan distribusi (UC-35).
Pembagian tanggung jawab: Tim IT → akses/autentikasi. Tim Pengadaan → data master Supplier termasuk penentuan jenisnya.
________________________________________
MODUL 2 — DATA MASTER (FRANCHISOR)
Dikelola oleh Franchisor, menjadi acuan bagi modul-modul lain:
1.	Jenis Layanan (jenis_layanan, UC-10/11/12) beserta komposisi bahan baku (jenis_layanan_bahan_baku, konsumsi_per_kg).
2.	Kategori Bahan Baku (kategori_bahan_baku, UC-19/20/21) dan Bahan Baku (bahan_baku, UC-13/14/15) yang dikelompokkan berdasarkan kategori tersebut.
3.	Mesin (mesin, UC-16/17/18) — data master tipe/model mesin (merk, tipe, kapasitas). Unit fisik per outlet (dengan nomor seri) dicatat terpisah di detail_mesin pada Modul 3/4 dan seterusnya.
________________________________________
MODUL 3 — PENGADAAN BARANG DARI SUPPLIER
Ruang lingkup: interaksi Tim Pengadaan ↔ Supplier untuk pengadaan bahan baku/mesin dari Supplier ke gudang pusat. Distribusi gudang pusat → outlet berada di modul lain.
3.1 Pembuatan dan Pengisian Detail PO
1.	Tim Pengadaan membuat PO (purchase_order), memilih Supplier dan jenis_po (UC-25). Status awal diajukan. Daftar Supplier yang ditampilkan pada form ini difilter berdasarkan jenis_supplier yang cocok dengan jenis_po — misal saat jenis_po = mesin, hanya Supplier dengan jenis_supplier = mesin yang muncul sebagai pilihan.
2.	Selama status diajukan, Tim Pengadaan dapat: 
o	Mengisi/menambah item (purchase_order_item_bahan_baku/_mesin) — UC-26, UC-27.
o	Menghapus PO beserta seluruh detailnya (UC-28) apabila belum dikirim ke Supplier.
3.	Begitu status berubah menjadi dikirim (lihat 3.2) atau lebih lanjut, PO tidak dapat lagi dihapus atau diubah.
3.2 Pengiriman PO ke Supplier
Tim Pengadaan mengklik "Kirim ke Supplier" (UC-30). Sistem memvalidasi PO memiliki minimal satu item, mengubah status menjadi dikirim, mengirim notifikasi ke Supplier, dan mengunci kemampuan edit/hapus dari sisi Tim Pengadaan.
3.3 Validasi PO oleh Supplier
Supplier memvalidasi setiap item satu per satu (UC-31). Karena PO sudah difilter berdasarkan jenis_supplier sejak awal (3.1), Supplier hanya akan melihat PO yang memang sesuai jenis dagangannya:
•	Disetujui → qty_disetujui = qty_diminta.
•	Disetujui Sebagian → qty_disetujui sesuai kemampuan pasok + alasan.
•	Ditolak → qty_disetujui = 0 + alasan.
Status PO keseluruhan dihitung dari agregasi status semua item:
•	Semua Disetujui → PO disetujui.
•	Semua Ditolak → PO ditolak.
•	Kombinasi apa pun → PO disetujui_sebagian.
PO dikunci, notifikasi hasil validasi dikirim ke Tim Pengadaan. Kekurangan pada item disetujui_sebagian/ditolak ditindaklanjuti lewat PO baru secara manual.
3.4 Distribusi Barang oleh Supplier
Untuk item yang disetujui, Supplier mencatat pengiriman (distribusi_barang + detail) berstatus dikirim atau dikirim_sebagian (UC-35). Sistem memvalidasi jumlah kirim ≤ jumlah disetujui, dan menolak pengiriman baru bila masih ada pengiriman sebelumnya yang belum dikonfirmasi. Supplier dapat melihat riwayat distribusinya, difilter tanggal/jenis item/tujuan, dan mengunduh PDF (UC-36).
3.5 Penerimaan Barang oleh Tim Pengadaan
Tim Pengadaan mencatat penerimaan (penerimaan_barang + detail, termasuk kondisi, diskon, ppn, dan nomor_seri khusus mesin) — UC-34:
•	Kondisi baik → stok pusat bertambah (stok_pusat_bahan_baku/stok_pusat_mesin, tercatat di mutasi_stok_pusat_*), stok Supplier berkurang, status distribusi_barang → diterima.
•	Ada barang cacat → dibuat retur_barang (+ detail: qty_retur, alasan, foto_bukti). Stok pusat hanya bertambah untuk barang tidak cacat. Status distribusi_barang → dikirim_sebagian, status retur_barang → menunggu_pengganti.
3.6 Proses Retur
1.	Supplier menginput data barang pengganti (UC-37) → status retur berubah menjadi pengganti_dikirim.
2.	Tim Pengadaan memeriksa dan mengonfirmasi (UC-38): 
o	Sesuai → stok pusat bertambah, status distribusi_barang → diterima, status retur_barang → selesai.
o	Tidak sesuai → Tim Pengadaan menolak; status retur tetap menunggu_pengganti, status distribusi tetap dikirim_sebagian, notifikasi penolakan dikirim ke Supplier.
3.7 Riwayat, Monitoring, dan Manajemen Stok Supplier
•	Tim Pengadaan memantau riwayat PO (UC-29) dengan status resmi: diajukan → dikirim → disetujui / disetujui_sebagian / ditolak → selesai, filter tanggal/tipe/status, unduh PDF.
•	Supplier dapat melihat detail stok miliknya sendiri — stok masuk, keluar, saat ini, dan riwayat mutasi (UC-32). Jenis item yang bisa dilihat otomatis dibatasi sesuai jenis_supplier miliknya — Supplier Bahan Baku hanya melihat stok_supplier_bahan_baku, Supplier Mesin hanya melihat stok_supplier_mesin.
•	Tim Pengadaan dapat melihat detail stok gudang pusat (UC-33) — agregat total per bahan baku/mesin, khusus untuk kebutuhan Tim Pengadaan (bukan per outlet; stok outlet akan menjadi modul tersendiri di luar cakupan Modul 1–4 ini).
•	Supplier dapat menambah (UC-39) dan menghapus (UC-40, hanya jika belum pernah didistribusikan) stok miliknya sendiri. Pilihan "jenis item" pada form tambah stok (bahan baku/mesin) otomatis dikunci/difilter sesuai jenis_supplier Supplier yang sedang login — mencegah Supplier Bahan Baku salah input stok Mesin, dan sebaliknya.
________________________________________
MODUL 4 — MANAJEMEN STOK OTOMATIS
Cakupan: stok gudang pusat (stok_pusat_bahan_baku, stok_pusat_mesin) dan stok Supplier (stok_supplier_bahan_baku, stok_supplier_mesin). Tidak mencakup stok outlet karena tabel tersebut belum ada di skema.
Prinsip utama:
1.	Stok Supplier berkurang saat distribusi_barang berubah menjadi dikirim/dikirim_sebagian (mutasi_stok_supplier_*).
2.	Stok Pusat bertambah saat penerimaan_barang dikonfirmasi diterima (mutasi_stok_pusat_*, mengacu ke penerimaan_barang_detail_*).
3.	Barang retur tidak menambah stok pusat sampai barang pengganti diterima dan retur_barang berstatus selesai.
4.	Tabel detail_mesin (unit fisik mesin dengan nomor_seri, outlet_id, dan status_id: aktif/digunakan/maintenance/nonaktif) disiapkan sebagai fondasi untuk modul selanjutnya (alokasi mesin ke outlet). Dalam cakupan Modul 1–4 ini, detail_mesin belum digunakan secara aktif — fokus Modul 3/4 cukup sampai pada stok_pusat_mesin (agregat jumlah mesin di gudang pusat).
5.	jenis_supplier pada tabel supplier berfungsi sebagai gerbang validasi (guard) di level aplikasi — memastikan setiap baris yang masuk ke stok_supplier_bahan_baku hanya berasal dari Supplier ber-jenis_supplier = bahan_baku, dan setiap baris di stok_supplier_mesin hanya dari Supplier ber-jenis_supplier = mesin. Validasi ini idealnya diterapkan di layer Controller/Form Request Laravel, bukan hanya di level UI, supaya tetap aman walau ada request langsung ke API.


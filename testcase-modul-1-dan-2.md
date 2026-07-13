# Test Case — WashHubb

**Modul 1:** Auth & Account Management  
**Modul 2:** Data Master  
**Modul 3:** Procurement  
**Modul 4:** Inventory  
**Modul 5:** Operasional Laundry  
**Modul 6:** Manajemen Franchise  
**Modul 7:** Dashboard  
**Tanggal:** 14 Juli 2026  
**Total Test Case:** 293

---

## A. Modul 1 — Auth

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_AUTH_001 | Login berhasil dengan email dan password yang valid | Email: admin@washhub.com, Password: password | Sistem menampilkan pesan login berhasil dan memberikan token akses | Pass |
| TC_AUTH_002 | Login gagal karena email tidak terdaftar di sistem | Email: tidakada@test.com, Password: password | Sistem menampilkan pesan "Kredensial tidak valid" | Pass |
| TC_AUTH_003 | Login gagal karena password yang dimasukkan salah | Email: admin@washhub.com, Password: salahpassword | Sistem menampilkan pesan "Kredensial tidak valid" | Pass |
| TC_AUTH_004 | Login gagal karena akun pengguna berstatus nonaktif | Email: user-nonaktif@test.com, Password: password | Sistem menampilkan pesan "Akun Anda nonaktif" | Pass |
| TC_AUTH_005 | Login gagal karena email tidak diisi | Email: (kosong), Password: password | Sistem menampilkan pesan bahwa email harus diisi | Pass |
| TC_AUTH_006 | Login gagal karena password tidak diisi | Email: admin@washhub.com, Password: (kosong) | Sistem menampilkan pesan bahwa password harus diisi | Pass |
| TC_AUTH_007 | Login gagal karena format email tidak valid | Email: bukanemail, Password: password | Sistem menampilkan pesan bahwa format email tidak valid | Pass |
| TC_AUTH_008 | Login gagal karena email dan password tidak diisi | Email: (kosong), Password: (kosong) | Sistem menampilkan pesan bahwa email dan password harus diisi | Pass |
| TC_AUTH_009 | Logout berhasil saat pengguna sudah login | Token akses valid | Sistem menampilkan pesan "Logout berhasil" | Pass |
| TC_AUTH_010 | Logout gagal karena pengguna belum login (tanpa token) | Tidak ada token | Sistem menolak akses (401 Unauthorized) | Pass |
| TC_AUTH_011 | Menampilkan data profil pengguna yang sedang login | Token akses valid | Sistem menampilkan data pengguna (nama, email, role, permissions) | Pass |
| TC_AUTH_012 | Menampilkan profil gagal karena tidak memiliki token | Tidak ada token | Sistem menolak akses (401 Unauthorized) | Pass |
| TC_AUTH_013 | Lupa password — mengecek email yang terdaftar | Email: admin@washhub.com | Sistem menampilkan pesan "Email ditemukan" dan nama pengguna | Pass |
| TC_AUTH_014 | Lupa password — mengecek email yang tidak terdaftar | Email: tidakada@test.com | Sistem menampilkan pesan "Email tidak ditemukan di sistem" | Pass |
| TC_AUTH_015 | Lupa password — email tidak diisi | Email: (kosong) | Sistem menampilkan pesan bahwa email harus diisi | Pass |
| TC_AUTH_016 | Reset password berhasil dengan konfirmasi password yang cocok | Email: admin@washhub.com, Password: passwordbaru123, Konfirmasi: passwordbaru123 | Sistem menampilkan pesan "Password berhasil direset" dan semua token dicabut | Pass |
| TC_AUTH_017 | Reset password gagal — password baru kurang dari 8 karakter | Email: admin@washhub.com, Password: abc, Konfirmasi: abc | Sistem menampilkan pesan bahwa password minimal 8 karakter | Pass |
| TC_AUTH_018 | Reset password gagal — konfirmasi password tidak cocok | Email: admin@washhub.com, Password: passwordbaru123, Konfirmasi: berbeda | Sistem menampilkan pesan bahwa konfirmasi password tidak cocok | Pass |
| TC_AUTH_019 | Reset password gagal — email tidak terdaftar | Email: tidakada@test.com, Password: passwordbaru123 | Sistem menampilkan pesan bahwa email tidak ditemukan | Pass |

## B. Modul 1 — Account Management (Users & Roles)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_USR_001 | Menampilkan daftar semua pengguna | Login sebagai admin, akses halaman users | Sistem menampilkan daftar pengguna dengan data nama, email, role, dan status | Pass |
| TC_USR_002 | Menampilkan daftar pengguna dengan pagination | Akses halaman users page 1 | Sistem menampilkan 15 data per halaman | Pass |
| TC_USR_003 | Menampilkan daftar pengguna gagal karena tidak login | Akses halaman users tanpa token | Sistem menolak akses (401 Unauthorized) | Pass |
| TC_USR_004 | Menampilkan daftar pengguna gagal karena tidak punya hak akses | Login sebagai user tanpa permission user-list | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass |
| TC_USR_005 | Menambah pengguna baru berhasil dengan data lengkap | Nama: User Baru, Email: baru@test.com, Password: password123, Role: admin_it | Sistem menyimpan dan menampilkan data pengguna baru | Pass |
| TC_USR_006 | Menambah pengguna gagal karena email sudah terdaftar | Nama: User Test, Email: admin@washhub.com, Password: password123 | Sistem menampilkan pesan bahwa email sudah digunakan | Pass |
| TC_USR_007 | Menambah pengguna gagal karena role tidak dipilih | Nama: User Test, Email: test@user.com, Password: password123, Role: (kosong) | Sistem menampilkan pesan bahwa role harus dipilih | Pass |
| TC_USR_008 | Menambah pengguna gagal karena nama tidak diisi | Nama: (kosong), Email: test@user.com, Password: password123 | Sistem menampilkan pesan bahwa nama harus diisi | Pass |
| TC_USR_009 | Menambah pengguna gagal karena password kurang dari 8 karakter | Nama: User, Email: test@user.com, Password: abc123 | Sistem menampilkan pesan bahwa password minimal 8 karakter | Pass |
| TC_USR_010 | Menambah pengguna gagal karena email tidak diisi | Nama: User, Email: (kosong), Password: password123 | Sistem menampilkan pesan bahwa email harus diisi | Pass |
| TC_USR_011 | Menambah pengguna dengan nomor telepon | Nama: User Telp, Email: telp@user.com, Password: password123, No. Telp: 081234567890 | Sistem menyimpan nomor telepon dengan benar | Pass |
| TC_USR_012 | Melihat detail pengguna berdasarkan ID | ID user: 1 | Sistem menampilkan detail lengkap pengguna (nama, email, role, status) | Pass |
| TC_USR_013 | Melihat detail pengguna gagal karena ID tidak ditemukan | ID user: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_USR_014 | Mengubah data pengguna berhasil | ID user: 1, Nama baru: Admin Updated | Sistem menyimpan perubahan dan menampilkan data baru | Pass |
| TC_USR_015 | Mengubah status pengguna menjadi nonaktif | ID user: 1, Status: nonaktif | Sistem mengubah status pengguna menjadi nonaktif | Pass |
| TC_USR_016 | Mengubah role pengguna | ID user: 1, Role: franchisor | Sistem mengubah role pengguna | Pass |
| TC_USR_017 | Mengubah email gagal karena sudah dipakai pengguna lain | ID user: 2, Email: (email milik user 1) | Sistem menampilkan pesan bahwa email sudah digunakan | Pass |
| TC_USR_018 | Mengubah data pengguna gagal karena ID tidak ditemukan | ID user: 999, Nama: Test | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_USR_019 | Menghapus pengguna berhasil | ID user yang baru dibuat | Sistem menghapus data dan menampilkan pesan sukses | Pass |
| TC_USR_020 | Menghapus pengguna gagal karena ID tidak ditemukan | ID user: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_USR_021 | Menghapus pengguna — token akses user tersebut tidak bisa dipakai lagi | ID user yang baru dibuat dan sudah login | Token user yang dihapus tidak dapat digunakan lagi | Pass |
| TC_USR_022 | Menghapus pengguna gagal karena tidak punya hak akses | Login sebagai franchisor | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass |
| TC_ROLE_001 | Menampilkan daftar semua role | Login sebagai admin, akses halaman roles | Sistem menampilkan daftar role beserta permission-nya | Pass |
| TC_ROLE_002 | Menampilkan halaman roles gagal karena tidak punya hak akses | Login sebagai manager_outlet | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass |
| TC_ROLE_003 | Menambah role baru berhasil (tanpa permission) | Kode: test_role, Label: Test Role | Sistem menyimpan role baru | Pass |
| TC_ROLE_004 | Menambah role baru dengan permission tertentu | Kode: role_po, Label: Role PO, Permissions: po-list, po-create | Sistem menyimpan role dengan permission yang dipilih | Pass |
| TC_ROLE_005 | Menambah role gagal karena kode sudah ada | Kode: admin_it (sudah terdaftar) | Sistem menampilkan pesan bahwa kode sudah digunakan | Pass |
| TC_ROLE_006 | Menambah role gagal karena kode tidak diisi | Kode: (kosong), Label: Test | Sistem menampilkan pesan bahwa kode harus diisi | Pass |
| TC_ROLE_007 | Menambah role gagal karena label tidak diisi | Kode: no_label, Label: (kosong) | Sistem menampilkan pesan bahwa label harus diisi | Pass |
| TC_ROLE_008 | Melihat detail role beserta permission-nya | ID role: 1 | Sistem menampilkan detail role dan daftar permission | Pass |
| TC_ROLE_009 | Melihat detail role gagal karena ID tidak ditemukan | ID role: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_ROLE_010 | Mengubah data role berhasil | ID role: 1, Label baru: Admin IT Updated | Sistem menyimpan perubahan dan menampilkan data baru | Pass |
| TC_ROLE_011 | Mengubah permission pada role | ID role: 1, Permissions: user-list, role-list | Sistem hanya menyimpan permission yang dipilih | Pass |
| TC_ROLE_012 | Mengubah role gagal karena kode sudah dipakai role lain | ID role: 2, Kode: admin_it | Sistem menampilkan pesan bahwa kode sudah digunakan | Pass |
| TC_ROLE_013 | Mengubah role gagal karena ID tidak ditemukan | ID role: 999, Label: Test | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_ROLE_014 | Menghapus role berhasil | ID role yang baru dibuat | Sistem menghapus data dan menampilkan pesan sukses | Pass |
| TC_ROLE_015 | Menghapus role gagal karena ID tidak ditemukan | ID role: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_PERM_001 | Menampilkan daftar semua permission | Login sebagai admin, akses halaman permissions | Sistem menampilkan daftar permission (kode, nama, modul) | Pass |
| TC_PERM_002 | Melihat detail permission | ID permission: 1 | Sistem menampilkan detail permission | Pass |
| TC_PERM_003 | Melihat detail permission gagal karena ID tidak ditemukan | ID permission: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_STAT_001 | Menampilkan daftar semua status | Login sebagai admin, akses halaman statuses | Sistem menampilkan daftar status berdasarkan konteks | Pass |
| TC_STAT_002 | Menampilkan status berdasarkan konteks tertentu | Konteks: akun | Sistem menampilkan status aktif dan nonaktif untuk akun | Pass |
| TC_STAT_003 | Menampilkan status untuk konteks supplier | Konteks: supplier | Sistem menampilkan status aktif dan nonaktif untuk supplier | Pass |

## C. Modul 1 — Account Management (Suppliers)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_SUP_001 | Menampilkan daftar semua supplier | Login sebagai admin, akses halaman suppliers | Sistem menampilkan daftar supplier dengan data pengguna dan status | Pass |
| TC_SUP_002 | Menampilkan halaman suppliers gagal karena tidak punya hak akses | Login sebagai manager_outlet | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass |
| TC_SUP_003 | Menambah supplier bahan baku berhasil | User: admin, Jenis: bahan_baku, Alamat: Jl. Test No.1, Bahan Baku: [1] | Sistem menyimpan supplier baru dengan jenis bahan baku | Pass |
| TC_SUP_004 | Menambah supplier mesin berhasil | User: admin, Jenis: mesin, Alamat: Jl. Mesin No.1, Mesin: [1] | Sistem menyimpan supplier baru dengan jenis mesin | Pass |
| TC_SUP_005 | Menambah supplier tanpa memilih item | User: admin, Jenis: bahan_baku, Alamat: Jl. Baru | Sistem menyimpan supplier dengan daftar item kosong | Pass |
| TC_SUP_006 | Menambah supplier gagal karena jenis supplier tidak valid | Jenis: invalid | Sistem menampilkan pesan bahwa jenis supplier harus bahan_baku atau mesin | Pass |
| TC_SUP_007 | Menambah supplier gagal karena user tidak ditemukan | User ID: 999 | Sistem menampilkan pesan bahwa user tidak valid | Pass |
| TC_SUP_008 | Menambah supplier gagal karena alamat tidak diisi | Alamat: (kosong) | Sistem menampilkan pesan bahwa alamat harus diisi | Pass |
| TC_SUP_009 | Melihat detail supplier bahan baku | ID supplier bahan baku | Sistem menampilkan detail supplier dan daftar bahan baku yang disuplai | Pass |
| TC_SUP_010 | Melihat detail supplier gagal karena ID tidak ditemukan | ID supplier: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_SUP_011 | Melihat daftar item yang disuplai oleh supplier bahan baku | ID supplier bahan baku | Sistem menampilkan daftar bahan baku (nama, satuan, harga) | Pass |
| TC_SUP_012 | Melihat daftar item yang disuplai oleh supplier mesin | ID supplier mesin | Sistem menampilkan daftar mesin (nama, kode mesin, harga) | Pass |
| TC_SUP_013 | Melihat daftar item dari supplier tanpa item terdaftar | ID supplier tanpa item | Sistem menampilkan daftar kosong | Pass |
| TC_SUP_014 | Mengubah data supplier berhasil | ID supplier: 1, Alamat baru: Jl. Baru No.5 | Sistem menyimpan perubahan alamat supplier | Pass |
| TC_SUP_015 | Mengubah daftar item yang disuplai supplier | ID supplier: 1, Bahan Baku ID: [2, 3] | Sistem memperbarui daftar item supplier | Pass |
| TC_SUP_016 | Mengubah supplier gagal karena ID tidak ditemukan | ID supplier: 999, Alamat: Test | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_SUP_017 | Menghapus supplier berhasil (soft delete) | ID supplier valid | Sistem menghapus data supplier dan menampilkan pesan sukses | Pass |
| TC_SUP_018 | Menghapus supplier — item-item terlepas dari supplier | ID supplier dengan item terdaftar | Sistem membersihkan relasi item-supplier | Pass |
| TC_SUP_019 | Menghapus supplier gagal karena ID tidak ditemukan | ID supplier: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_SUP_020 | Memastikan supplier yang sudah dihapus tidak muncul di daftar | ID supplier yang sudah dihapus | Sistem tidak menampilkan supplier tersebut di daftar | Pass |

## D. Modul 2 — Data Master (Kategori & Bahan Baku)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_CAT_001 | Menampilkan daftar semua kategori | Login sebagai admin, akses halaman kategori | Sistem menampilkan daftar kategori dengan jumlah bahan baku per kategori | Pass |
| TC_CAT_002 | Menampilkan halaman kategori gagal karena tidak punya hak akses | Login sebagai supplier | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass |
| TC_CAT_003 | Menambah kategori baru berhasil | Nama: Kategori Test | Sistem menyimpan kategori baru | Pass |
| TC_CAT_004 | Menambah kategori gagal karena nama sudah ada | Nama: (nama yang sudah terdaftar) | Sistem menampilkan pesan bahwa nama sudah digunakan | Pass |
| TC_CAT_005 | Menambah kategori gagal karena nama tidak diisi | Nama: (kosong) | Sistem menampilkan pesan bahwa nama harus diisi | Pass |
| TC_CAT_006 | Melihat detail kategori | ID kategori: 1 | Sistem menampilkan nama kategori dan jumlah bahan baku | Pass |
| TC_CAT_007 | Melihat detail kategori gagal karena ID tidak ditemukan | ID kategori: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_CAT_008 | Mengubah nama kategori berhasil | ID kategori: 1, Nama: Kategori Updated | Sistem menyimpan perubahan nama kategori | Pass |
| TC_CAT_009 | Mengubah kategori gagal karena nama sudah dipakai kategori lain | ID kategori: 1, Nama: (nama kategori lain) | Sistem menampilkan pesan bahwa nama sudah digunakan | Pass |
| TC_CAT_010 | Mengubah kategori gagal karena ID tidak ditemukan | ID kategori: 999, Nama: Test | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_CAT_011 | Menghapus kategori berhasil | ID kategori yang baru dibuat | Sistem menghapus kategori | Pass |
| TC_CAT_012 | Menghapus kategori — bahan baku di dalamnya ikut terhapus | ID kategori yang memiliki bahan baku | Sistem menghapus kategori beserta bahan baku di dalamnya | Pass |
| TC_CAT_013 | Menghapus kategori gagal karena ID tidak ditemukan | ID kategori: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_MAT_001 | Menampilkan daftar semua bahan baku | Login sebagai admin, akses halaman materials | Sistem menampilkan daftar bahan baku dengan kategori dan status | Pass |
| TC_MAT_002 | Menampilkan halaman bahan baku gagal karena tidak punya hak akses | Login sebagai user tanpa permission material-list | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass |
| TC_MAT_003 | Menambah bahan baku berhasil | Kategori: 1, Nama: Bahan Test, Satuan: kg, Harga: 15000 | Sistem menyimpan bahan baku baru | Pass |
| TC_MAT_004 | Menambah bahan baku — status otomatis aktif | Data valid tanpa status | Sistem mengisi status aktif secara otomatis | Pass |
| TC_MAT_005 | Menambah bahan baku gagal karena kategori tidak valid | ID kategori: 999 | Sistem menampilkan pesan bahwa kategori tidak valid | Pass |
| TC_MAT_006 | Menambah bahan baku gagal karena nama tidak diisi | Nama: (kosong) | Sistem menampilkan pesan bahwa nama harus diisi | Pass |
| TC_MAT_007 | Menambah bahan baku gagal karena satuan tidak diisi | Satuan: (kosong) | Sistem menampilkan pesan bahwa satuan harus diisi | Pass |
| TC_MAT_008 | Menambah bahan baku gagal karena harga negatif | Harga: -1000 | Sistem menampilkan pesan bahwa harga tidak boleh negatif | Pass |
| TC_MAT_009 | Melihat detail bahan baku | ID bahan baku: 1 | Sistem menampilkan detail (nama, kategori, satuan, harga, status) | Pass |
| TC_MAT_010 | Melihat detail bahan baku gagal karena ID tidak ditemukan | ID bahan baku: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_MAT_011 | Mengubah data bahan baku berhasil | ID bahan baku: 1, Nama: Bahan Updated | Sistem menyimpan perubahan | Pass |
| TC_MAT_012 | Mengubah kategori bahan baku | ID bahan baku: 1, Kategori: 2 | Sistem mengubah kategori bahan baku | Pass |
| TC_MAT_013 | Mengubah harga standar bahan baku | ID bahan baku: 1, Harga: 25000 | Sistem mengubah harga standar | Pass |
| TC_MAT_014 | Mengubah bahan baku gagal karena kategori tidak valid | ID bahan baku: 1, Kategori: 999 | Sistem menampilkan pesan bahwa kategori tidak valid | Pass |
| TC_MAT_015 | Mengubah bahan baku gagal karena ID tidak ditemukan | ID bahan baku: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_MAT_016 | Menghapus bahan baku berhasil | ID bahan baku valid | Sistem menghapus data bahan baku | Pass |
| TC_MAT_017 | Menghapus bahan baku gagal karena ID tidak ditemukan | ID bahan baku: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |

## E. Modul 2 — Data Master (Jenis Layanan)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_SVC_001 | Menampilkan daftar semua jenis layanan | Login sebagai admin, akses halaman services | Sistem menampilkan daftar layanan dengan material dan status | Pass |
| TC_SVC_002 | Menampilkan halaman layanan gagal karena tidak punya hak akses | Login sebagai supplier | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass |
| TC_SVC_003 | Menambah jenis layanan baru berhasil | Nama: Layanan Test, Harga per Kg: 50000 | Sistem menyimpan jenis layanan baru | Pass |
| TC_SVC_004 | Menambah layanan — status otomatis aktif | Data valid tanpa status | Sistem mengisi status aktif secara otomatis | Pass |
| TC_SVC_005 | Menambah layanan gagal karena nama sudah ada | Nama: (nama yang sudah terdaftar) | Sistem menampilkan pesan bahwa nama sudah digunakan | Pass |
| TC_SVC_006 | Menambah layanan gagal karena harga negatif | Harga per Kg: -5000 | Sistem menampilkan pesan bahwa harga tidak boleh negatif | Pass |
| TC_SVC_007 | Menambah layanan gagal karena nama tidak diisi | Nama: (kosong) | Sistem menampilkan pesan bahwa nama harus diisi | Pass |
| TC_SVC_008 | Melihat detail jenis layanan | ID layanan: 1 | Sistem menampilkan detail layanan (nama, harga, status, material) | Pass |
| TC_SVC_009 | Melihat detail layanan gagal karena ID tidak ditemukan | ID layanan: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_SVC_010 | Mengubah data layanan berhasil | ID layanan: 1, Nama: Layanan Updated | Sistem menyimpan perubahan | Pass |
| TC_SVC_011 | Mengubah harga layanan | ID layanan: 1, Harga per Kg: 75000 | Sistem mengubah harga layanan | Pass |
| TC_SVC_012 | Mengubah layanan gagal karena ID tidak ditemukan | ID layanan: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_SVC_013 | Menghapus layanan berhasil | ID layanan valid | Sistem menghapus data layanan | Pass |
| TC_SVC_014 | Menghapus layanan gagal karena ID tidak ditemukan | ID layanan: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_SVC_015 | Menambahkan material ke layanan berhasil | ID layanan: 1, Material ID: 1, Jumlah Konsumsi: 0.5 | Sistem menambahkan material ke layanan | Pass |
| TC_SVC_016 | Menambahkan material gagal karena material tidak ditemukan | ID layanan: 1, Material ID: 999 | Sistem menampilkan pesan bahwa material tidak valid | Pass |
| TC_SVC_017 | Menambahkan material gagal karena jumlah konsumsi 0 | ID layanan: 1, Material ID: 1, Jumlah: 0 | Sistem menampilkan pesan bahwa jumlah konsumsi harus lebih dari 0 | Pass |
| TC_SVC_018 | Menambahkan material yang sama ke layanan (duplikat) | ID layanan: 1, Material ID: 1 (sama) | Sistem tidak membuat duplikat, data tetap aman | Pass |
| TC_SVC_019 | Menghapus material dari layanan berhasil | ID layanan: 1, Material ID: 1 | Sistem menghapus material dari layanan | Pass |
| TC_SVC_020 | Menghapus material yang tidak terdaftar di layanan | ID layanan: 1, Material ID: 999 | Sistem tetap berhasil (tidak error) | Pass |

## F. Modul 2 — Data Master (Mesin)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_MCH_001 | Menampilkan daftar semua mesin | Login sebagai admin, akses halaman machines | Sistem menampilkan daftar mesin dengan status | Pass |
| TC_MCH_002 | Menampilkan halaman mesin gagal karena tidak punya hak akses | Login sebagai supplier | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Fail |
| TC_MCH_003 | Menambah mesin baru berhasil (minimal) | Nama: Mesin Test, Kode Mesin: MCH-001 | Sistem menyimpan mesin baru | Pass |
| TC_MCH_004 | Menambah mesin dengan data lengkap | Nama: Mesin Lengkap, Kode: MCH-002, Merk: MerkX, Tipe: TipeY, Kapasitas: 100, Harga: 5000000 | Sistem menyimpan semua data dengan benar | Pass |
| TC_MCH_005 | Menambah mesin — status otomatis aktif | Data valid tanpa status | Sistem mengisi status aktif secara otomatis | Pass |
| TC_MCH_006 | Menambah mesin gagal karena kode mesin sudah ada | Kode Mesin: MCH-001 (sudah terdaftar) | Sistem menampilkan pesan bahwa kode mesin sudah digunakan | Pass |
| TC_MCH_007 | Menambah mesin gagal karena nama tidak diisi | Nama: (kosong), Kode: MCH-010 | Sistem menampilkan pesan bahwa nama harus diisi | Pass |
| TC_MCH_008 | Menambah mesin gagal karena kode mesin tidak diisi | Nama: Mesin, Kode: (kosong) | Sistem menampilkan pesan bahwa kode mesin harus diisi | Pass |
| TC_MCH_009 | Menambah mesin gagal karena kapasitas negatif | Kapasitas: -5 | Sistem menampilkan pesan bahwa kapasitas tidak boleh negatif | Pass |
| TC_MCH_010 | Melihat detail mesin | ID mesin: 1 | Sistem menampilkan detail mesin (nama, kode, merk, tipe, kapasitas, harga, status) | Pass |
| TC_MCH_011 | Melihat detail mesin gagal karena ID tidak ditemukan | ID mesin: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_MCH_012 | Mengubah data mesin berhasil | ID mesin: 1, Nama: Mesin Updated | Sistem menyimpan perubahan | Pass |
| TC_MCH_013 | Mengubah seluruh data mesin | ID mesin: 1, Nama: Baru, Merk: MerkBaru, Tipe: TipeBaru, Kapasitas: 200, Harga: 10000000 | Sistem mengubah semua data mesin | Pass |
| TC_MCH_014 | Mengubah mesin gagal karena kode mesin sudah dipakai mesin lain | ID mesin: 1, Kode: (kode mesin lain) | Sistem menampilkan pesan bahwa kode mesin sudah digunakan | Pass |
| TC_MCH_015 | Mengubah mesin gagal karena ID tidak ditemukan | ID mesin: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_MCH_016 | Menghapus mesin berhasil | ID mesin valid | Sistem menghapus data mesin | Pass |
| TC_MCH_017 | Menghapus mesin gagal karena ID tidak ditemukan | ID mesin: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_MCH_018 | Memastikan mesin yang sudah dihapus tidak muncul di daftar | ID mesin yang sudah dihapus | Sistem tidak menampilkan mesin tersebut di daftar | Pass |

---

## G. Modul 3 — Procurement (Purchase Order)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_PO_001 | Menampilkan daftar semua PO | Login sebagai admin, akses halaman PO | Sistem menampilkan daftar PO dengan data supplier, status, dan total nilai | Pass |
| TC_PO_002 | Menampilkan daftar PO untuk supplier (hanya PO miliknya) | Login sebagai supplier | Sistem hanya menampilkan PO yang ditujukan ke supplier tersebut | Pass |
| TC_PO_003 | Menampilkan daftar PO dengan filter status | Filter: status=diajukan | Sistem hanya menampilkan PO dengan status diajukan | Pass |
| TC_PO_004 | Menambah PO bahan baku berhasil | Supplier: 1, Jenis: bahan_baku, Items: [{item_id: 1, jumlah: 10, harga_satuan: 5000}] | Sistem menyimpan PO baru dengan status diajukan | Pass |
| TC_PO_005 | Menambah PO mesin berhasil | Supplier: 1, Jenis: mesin, Items: [{item_id: 1, jumlah: 2, harga_satuan: 5000000}] | Sistem menyimpan PO mesin baru | Pass |
| TC_PO_006 | Menambah PO — total_nilai otomatis dihitung | Items: 2 item @10000 x 5 | Sistem menghitung total_nilai = 100000 | Pass |
| TC_PO_007 | Menambah PO gagal karena supplier tidak valid | Supplier ID: 999 | Sistem menampilkan pesan bahwa supplier tidak valid | Pass |
| TC_PO_008 | Menambah PO gagal karena items kosong | Items: [] | Sistem menampilkan pesan bahwa items harus diisi | Pass |
| TC_PO_009 | Menambah PO gagal karena jumlah 0 | Items: [{item_id: 1, jumlah: 0, harga: 5000}] | Sistem menampilkan pesan bahwa jumlah minimal 1 | Pass |
| TC_PO_010 | Menambah PO gagal karena harga negatif | Items: [{item_id: 1, jumlah: 1, harga: -1000}] | Sistem menampilkan pesan bahwa harga tidak boleh negatif | Pass |
| TC_PO_011 | Melihat detail PO | ID PO: 1 | Sistem menampilkan detail PO termasuk item dan distribusi | Pass |
| TC_PO_012 | Melihat detail PO gagal karena ID tidak ditemukan | ID PO: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_PO_013 | Mengubah PO berhasil (status diajukan) | ID PO diajukan, data baru | Sistem menyimpan perubahan PO | Pass |
| TC_PO_014 | Mengubah PO gagal karena status bukan diajukan | ID PO yang sudah dikirim | Sistem menampilkan pesan bahwa hanya PO diajukan yang bisa diedit | Pass |
| TC_PO_015 | Menghapus PO berhasil (status diajukan) | ID PO diajukan | Sistem menghapus PO | Pass |
| TC_PO_016 | Menghapus PO gagal karena status bukan diajukan | ID PO yang sudah dikirim | Sistem menampilkan pesan bahwa hanya PO diajukan yang bisa dihapus | Pass |
| TC_PO_017 | Menghapus PO gagal karena ID tidak ditemukan | ID PO: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_PO_018 | Kirim PO ke supplier berhasil | ID PO diajukan | Sistem mengubah status PO menjadi dikirim | Pass |
| TC_PO_019 | Kirim PO gagal karena status bukan diajukan | ID PO yang sudah dikirim | Sistem menampilkan pesan bahwa hanya PO diajukan yang bisa dikirim | Pass |
| TC_PO_020 | Supplier validasi PO berhasil | ID PO dikirim, items: [{item_id: 1, status: diterima}] | Sistem mengubah status item PO | Pass |
| TC_PO_021 | Supplier validasi PO gagal karena bukan PO miliknya | ID PO milik supplier lain | Sistem menampilkan pesan tidak punya akses | Pass |

## H. Modul 3 — Procurement (Supplier Stock)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_SSTOCK_001 | Menampilkan daftar stok supplier | Login sebagai supplier | Sistem menampilkan daftar stok bahan baku/mesin milik supplier | Pass |
| TC_SSTOCK_002 | Menambah stok supplier berhasil | Items: [{item_id: 1, jumlah: 100}] | Sistem menambah stok dan mencatat mutasi masuk | Pass |
| TC_SSTOCK_003 | Menambah stok — stok_saat_ini bertambah | Stok awal: 50, tambah: 30 | Sistem menghitung stok_saat_ini = 80 | Pass |
| TC_SSTOCK_004 | Menambah stok — mutasi tercatat | Tambah stok: 50 | Sistem membuat record mutasi jenis_masuk | Pass |
| TC_SSTOCK_005 | Menambah stok gagal karena item_id tidak valid | Items: [{item_id: 999, jumlah: 10}] | Sistem menampilkan pesan error validasi | Pass |
| TC_SSTOCK_006 | Menambah stok gagal karena jumlah 0 | Items: [{item_id: 1, jumlah: 0}] | Sistem menampilkan pesan bahwa jumlah minimal 1 | Pass |
| TC_SSTOCK_007 | Melihat detail stok supplier | ID stok: 1 | Sistem menampilkan detail stok dan riwayat mutasi | Pass |
| TC_SSTOCK_008 | Melihat detail stok — riwayat masuk manual | ID stok dengan mutasi masuk | Sistem menampilkan riwayat tambah stok manual | Pass |
| TC_SSTOCK_009 | Melihat detail stok — riwayat keluar distribusi | ID stok dengan distribusi | Sistem menampilkan riwayat distribusi keluar | Pass |
| TC_SSTOCK_010 | Update stok supplier berhasil (tambah stok) | ID stok: 1, jumlah: 25 | Sistem menambah stok dan mencatat mutasi | Pass |
| TC_SSTOCK_011 | Hapus stok supplier berhasil (belum pernah didistribusikan) | ID stok yang belum pernah didistribusikan | Sistem menghapus stok | Pass |
| TC_SSTOCK_012 | Hapus stok supplier gagal karena sudah pernah didistribusikan | ID stok yang sudah pernah didistribusikan | Sistem menampilkan pesan bahwa stok sudah pernah didistribusikan | Pass |
| TC_SSTOCK_013 | Dropdown bahan baku menampilkan data master | Buka form tambah stok | Sistem menampilkan daftar bahan baku dari data master | Pass |
| TC_SSTOCK_014 | Dropdown mesin menampilkan data master | Login sebagai supplier mesin, buka form | Sistem menampilkan daftar mesin dari data master | Pass |

## I. Modul 3 — Procurement (Distribusi)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_DIST_001 | Menampilkan daftar distribusi | Login sebagai procurement | Sistem menampilkan daftar distribusi dengan status | Pass |
| TC_DIST_002 | Menampilkan daftar distribusi untuk supplier | Login sebagai supplier | Sistem hanya menampilkan distribusi terkait PO supplier | Pass |
| TC_DIST_003 | Membuat distribusi berhasil | PO ID, items: [{po_item_id: 1, jumlah_kirim: 10}] | Sistem menyimpan distribusi baru | Pass |
| TC_DIST_004 | Membuat distribusi gagal karena PO belum valid | PO yang belum divalidasi supplier | Sistem menampilkan pesan bahwa PO belum valid | Pass |
| TC_DIST_005 | Membuat distribusi gagal karena jumlah kirim melebihi PO | jumlah_kirim > jumlah PO | Sistem menampilkan pesan bahwa jumlah kirim melebihi PO | Pass |
| TC_DIST_006 | Melihat detail distribusi | ID distribusi: 1 | Sistem menampilkan detail distribusi dan item | Pass |
| TC_DIST_007 | Melihat detail distribusi gagal karena ID tidak ditemukan | ID distribusi: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_DIST_008 | Konfirmasi penerimaan distribusi berhasil | ID distribusi, items diterima | Sistem mengubah status distribusi menjadi diterima | Pass |
| TC_DIST_009 | Konfirmasi penerimaan — stok pusat bertambah | Terima distribusi 10 unit | Sistem menambah stok pusat sejumlah diterima | Pass |
| TC_DIST_010 | Konfirmasi penerimaan gagal karena bukan penerima | Login sebagai user tanpa hak | Sistem menampilkan pesan tidak punya akses | Pass |

## J. Modul 3 — Procurement (Penerimaan & Retur)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_RCV_001 | Menampilkan daftar penerimaan | Login sebagai procurement | Sistem menampilkan daftar penerimaan barang | Pass |
| TC_RCV_002 | Membuat penerimaan berhasil | Distribusi ID, detail items | Sistem menyimpan penerimaan baru | Pass |
| TC_RCV_003 | Membuat penerimaan — ada item cacat | Items: [{qty_diterima: 8, kondisi: baik}, {qty_diterima: 2, kondisi: cacat}] | Sistem mencatat item baik dan cacat | Pass |
| TC_RCV_004 | Melihat detail penerimaan | ID penerimaan: 1 | Sistem menampilkan detail penerimaan | Pass |
| TC_RCV_005 | Menampilkan daftar retur | Login sebagai procurement | Sistem menampilkan daftar retur barang | Pass |
| TC_RCV_006 | Membuat retur berhasil | Penerimaan ID, items retur | Sistem menyimpan retur baru | Pass |
| TC_RCV_007 | Membuat retur gagal karena penerimaan tidak valid | Penerimaan ID: 999 | Sistem menampilkan pesan data tidak ditemukan | Pass |
| TC_RCV_008 | Melihat detail retur | ID retur: 1 | Sistem menampilkan detail retur | Pass |
| TC_RCV_009 | Kirim pengganti retur berhasil | ID retur | Sistem mengirim barang pengganti | Pass |
| TC_RCV_010 | Konfirmasi retur berhasil | ID retur | Sistem mengkonfirmasi retur | Pass |

## K. Modul 4 — Inventory

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_INV_001 | Menampilkan daftar stok bahan baku pusat | Login sebagai procurement, type=bahan | Sistem menampilkan daftar stok bahan baku pusat | Pass |
| TC_INV_002 | Menampilkan daftar stok mesin pusat | Login sebagai procurement, type=mesin | Sistem menampilkan daftar stok mesin pusat | Pass |
| TC_INV_003 | Menampilkan stok pusat gagal karena tidak punya akses | Login sebagai user tanpa permission stock-list | Sistem menampilkan pesan tidak punya akses (403) | Pass |
| TC_INV_004 | Melihat detail stok bahan baku pusat | Type: bahan, ID: 1 | Sistem menampilkan detail stok (nama, satuan, stok_masuk, stok_keluar, stok_saat_ini) | Pass |
| TC_INV_005 | Melihat detail stok mesin pusat | Type: mesin, ID: 1 | Sistem menampilkan detail stok mesin | Pass |
| TC_INV_006 | Melihat detail stok gagal karena ID tidak ditemukan | Type: bahan, ID: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_INV_007 | Menampilkan daftar mutasi stok | Login sebagai procurement | Sistem menampilkan daftar mutasi stok | Pass |
| TC_INV_008 | Membuat mutasi masuk berhasil | Jenis: masuk, item, jumlah | Sistem menyimpan mutasi dan menambah stok | Pass |
| TC_INV_009 | Membuat mutasi keluar berhasil | Jenis: keluar, item, jumlah | Sistem menyimpan mutasi dan mengurangi stok | Pass |
| TC_INV_010 | Membuat mutasi gagal karena stok tidak cukup | Keluar 100, stok tersedia 50 | Sistem menampilkan pesan bahwa stok tidak cukup | Pass |
| TC_INV_011 | Membuat mutasi gagal karena item tidak valid | Item ID: 999 | Sistem menampilkan pesan error validasi | Pass |
| TC_INV_012 | Membuat mutasi gagal karena jumlah 0 | Jumlah: 0 | Sistem menampilkan pesan bahwa jumlah harus lebih dari 0 | Pass |

## L. Modul 5 — Operasional (Order Cucian)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_ORD_001 | Menampilkan daftar order cucian | Login sebagai manager outlet | Sistem menampilkan daftar order cucian | Pass |
| TC_ORD_002 | Membuat order cucian berhasil | Data order lengkap | Sistem menyimpan order baru | Pass |
| TC_ORD_003 | Membuat order — layanan dan item tercatat | Layanan: [1], items: [{bahan_baku_id: 1, jumlah: 2}] | Sistem menyimpan detail layanan dan item | Pass |
| TC_ORD_004 | Membuat order gagal karena data tidak lengkap | Data kosong | Sistem menampilkan pesan error validasi | Pass |
| TC_ORD_005 | Melihat detail order cucian | ID order: 1 | Sistem menampilkan detail order (pelanggan, layanan, item, status) | Pass |
| TC_ORD_006 | Melihat detail order gagal karena ID tidak ditemukan | ID order: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_ORD_007 | Mengubah status order berhasil | ID order, status baru | Sistem mengubah status order | Pass |
| TC_ORD_008 | Mengubah order gagal karena ID tidak ditemukan | ID order: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |

## M. Modul 5 — Operasional (Permintaan & Distribusi Stok Outlet)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_PSTOK_001 | Menampilkan daftar permintaan stok outlet | Login sebagai manager outlet | Sistem menampilkan daftar permintaan stok | Pass |
| TC_PSTOK_002 | Membuat permintaan stok berhasil | Items: [{bahan_baku_id: 1, jumlah: 50}] | Sistem menyimpan permintaan baru | Pass |
| TC_PSTOK_003 | Membuat permintaan stok gagal karena items kosong | Items: [] | Sistem menampilkan pesan bahwa items harus diisi | Pass |
| TC_PSTOK_004 | Melihat detail permintaan stok | ID permintaan: 1 | Sistem menampilkan detail permintaan | Pass |
| TC_PSTOK_005 | Menghapus permintaan stok berhasil | ID permintaan valid | Sistem menghapus permintaan | Pass |
| TC_PSTOK_006 | Validasi permintaan stok oleh procurement | ID permintaan, items validasi | Sistem mengubah status item permintaan | Pass |
| TC_PSTOK_007 | Menampilkan daftar distribusi outlet | Login sebagai procurement | Sistem menampilkan daftar distribusi ke outlet | Pass |
| TC_PSTOK_008 | Membuat distribusi outlet berhasil | Permintaan ID, items kirim | Sistem menyimpan distribusi outlet | Pass |
| TC_PSTOK_009 | Melihat detail distribusi outlet | ID distribusi: 1 | Sistem menampilkan detail distribusi outlet | Pass |
| TC_PSTOK_010 | Konfirmasi penerimaan distribusi outlet | ID distribusi | Sistem mengubah status menjadi diterima | Pass |
| TC_PSTOK_011 | Menampilkan daftar penerimaan stok outlet | Login sebagai manager outlet | Sistem menampilkan daftar penerimaan | Pass |
| TC_PSTOK_012 | Melihat detail penerimaan stok outlet | ID penerimaan: 1 | Sistem menampilkan detail penerimaan | Pass |

## N. Modul 5 — Operasional (Jadwal Service & Shift)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_JSVC_001 | Menampilkan daftar jadwal service mesin | Login sebagai manager outlet | Sistem menampilkan daftar jadwal service | Pass |
| TC_JSVC_002 | Membuat jadwal service berhasil | Mesin ID, tanggal, keterangan | Sistem menyimpan jadwal service baru | Pass |
| TC_JSVC_003 | Membuat jadwal service gagal karena mesin tidak valid | Mesin ID: 999 | Sistem menampilkan pesan error validasi | Pass |
| TC_JSVC_004 | Melihat detail jadwal service | ID jadwal: 1 | Sistem menampilkan detail jadwal service | Pass |
| TC_JSVC_005 | Validasi jadwal service oleh franchisor | ID jadwal | Sistem mengubah status jadwal | Pass |
| TC_JSVC_006 | Menyelesaikan jadwal service | ID jadwal | Sistem mengubah status menjadi selesai | Pass |
| TC_JSVC_007 | Menampilkan daftar jadwal shift staf | Login sebagai manager outlet | Sistem menampilkan daftar jadwal shift | Pass |
| TC_JSVC_008 | Membuat jadwal shift berhasil | Staf, tanggal, jam shift | Sistem menyimpan jadwal shift baru | Pass |
| TC_JSVC_009 | Membuat jadwal shift gagal karena data tidak lengkap | Data kosong | Sistem menampilkan pesan error validasi | Pass |
| TC_JSVC_010 | Melihat detail jadwal shift | ID jadwal: 1 | Sistem menampilkan detail jadwal shift | Pass |
| TC_JSVC_011 | Mengubah jadwal shift berhasil | ID jadwal, data baru | Sistem menyimpan perubahan jadwal | Pass |
| TC_JSVC_012 | Melihat history jadwal shift | Akses endpoint history | Sistem menampilkan history jadwal shift | Pass |

## O. Modul 6 — Manajemen Franchise (Outlet)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_OUT_001 | Menampilkan daftar outlet | Login sebagai franchisor | Sistem menampilkan daftar outlet dengan data franchise dan manager | Pass |
| TC_OUT_002 | Menampilkan daftar outlet untuk franchisee | Login sebagai franchisee | Sistem hanya menampilkan outlet milik franchisee | Pass |
| TC_OUT_003 | Menambah outlet berhasil | Nama, kode_outlet, alamat, franchise_id | Sistem menyimpan outlet baru | Pass |
| TC_OUT_004 | Menambah outlet — franchise user otomatis ter-sync | Franchise ID dengan user | Sistem menambahkan franchise user ke user_outlets | Pass |
| TC_OUT_005 | Menambah outlet — manager outlet ter-sync | manager_outlet_id diisi | Sistem menambahkan manager ke user_outlets | Pass |
| TC_OUT_006 | Menambah outlet gagal karena kode_outlet sudah ada | Kode: (sudah terdaftar) | Sistem menampilkan pesan bahwa kode sudah digunakan | Pass |
| TC_OUT_007 | Menambah outlet gagal karena franchise tidak valid | Franchise ID: 999 | Sistem menampilkan pesan bahwa franchise tidak valid | Pass |
| TC_OUT_008 | Menambah outlet gagal karena nama tidak diisi | Nama: (kosong) | Sistem menampilkan pesan bahwa nama harus diisi | Pass |
| TC_OUT_009 | Melihat detail outlet | ID outlet: 1 | Sistem menampilkan detail outlet (franchise, manager, stok) | Pass |
| TC_OUT_010 | Melihat detail outlet — stok outlet tampil | ID outlet dengan stok | Sistem menampilkan stok bahan baku dan mesin outlet | Pass |
| TC_OUT_011 | Mengubah data outlet berhasil | ID outlet, data baru | Sistem menyimpan perubahan outlet | Pass |
| TC_OUT_012 | Mengubah outlet gagal karena ID tidak ditemukan | ID outlet: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_OUT_013 | Menampilkan dropdown franchise | Akses endpoint franchises | Sistem menampilkan daftar franchise | Pass |
| TC_OUT_014 | Menampilkan dropdown manajer operasional | Akses endpoint manajer-operasionals | Sistem menampilkan daftar manajer operasional | Pass |

## P. Modul 6 — Manajemen Franchise (Loyalti)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_LOY_001 | Menampilkan daftar program loyalti | Login sebagai franchisor | Sistem menampilkan daftar program loyalti | Pass |
| TC_LOY_002 | Membuat program loyalti berhasil | Nama, deskripsi, syarat | Sistem menyimpan program loyalti baru | Pass |
| TC_LOY_003 | Membuat program loyalti gagal karena data tidak lengkap | Data kosong | Sistem menampilkan pesan error validasi | Pass |
| TC_LOY_004 | Melihat detail program loyalti | ID loyalti: 1 | Sistem menampilkan detail program loyalti | Pass |
| TC_LOY_005 | Melihat detail loyalti gagal karena ID tidak ditemukan | ID loyalti: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass |
| TC_LOY_006 | Evaluasi loyalti franchisee berhasil | ID loyalti, data evaluasi | Sistem mencatat hasil evaluasi | Pass |
| TC_LOY_007 | Set bonus loyalti berhasil | ID loyalti, jumlah bonus | Sistem menetapkan bonus untuk franchisee | Pass |
| TC_LOY_008 | Cairkan bonus loyalti berhasil | ID loyalti | Sistem mengajukan pencairan bonus | Pass |
| TC_LOY_009 | Konfirmasi pencairan bonus berhasil | ID loyalti | Sistem mengkonfirmasi pencairan bonus | Pass |
| TC_LOY_010 | Konfirmasi pencairan gagal karena status tidak valid | ID loyalti yang belum diajukan | Sistem menampilkan pesan bahwa status tidak valid | Pass |

## Q. Modul 7 — Dashboard

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_DASH_001 | Menampilkan dashboard franchisor | Login sebagai franchisor | Sistem menampilkan data ringkasan franchise dan outlet | Pass |
| TC_DASH_002 | Menampilkan dashboard pengadaan | Login sebagai procurement | Sistem menampilkan data ringkasan PO dan distribusi | Pass |
| TC_DASH_003 | Menampilkan dashboard supplier | Login sebagai supplier | Sistem menampilkan data ringkasan stok dan distribusi | Pass |
| TC_DASH_004 | Menampilkan dashboard franchisee | Login sebagai franchisee | Sistem menampilkan data ringkasan outlet dan order | Pass |
| TC_DASH_005 | Menampilkan dashboard manager outlet | Login sebagai manager outlet | Sistem menampilkan data ringkasan operasional outlet | Pass |
| TC_DASH_006 | Dashboard gagal karena tidak login | Akses tanpa token | Sistem menolak akses (401 Unauthorized) | Pass |
| TC_DASH_007 | Dashboard franchisor menampilkan jumlah outlet aktif | Login sebagai franchisor | Sistem menampilkan jumlah outlet dengan status aktif | Pass |
| TC_DASH_008 | Dashboard pengadaan menampilkan PO pending | Login sebagai procurement | Sistem menampilkan jumlah PO dengan status diajukan | Pass |
| TC_DASH_009 | Dashboard supplier menampilkan stok saat ini | Login sebagai supplier | Sistem menampilkan ringkasan stok supplier | Pass |
| TC_DASH_010 | Dashboard franchisee menampilkan order hari ini | Login sebagai franchisee | Sistem menampilkan jumlah order hari ini | Pass |

---

## Ringkasan

| Bagian | Modul | Grup | Jumlah TC |
|--------|-------|------|----------:|
| A | 1 - Auth | Login, Logout, Profile, Forgot Password | 19 |
| B | 1 - Account | Users, Roles, Permissions, Statuses | 43 |
| C | 1 - Account | Suppliers | 20 |
| D | 2 - Data Master | Kategori, Bahan Baku | 30 |
| E | 2 - Data Master | Jenis Layanan | 20 |
| F | 2 - Data Master | Mesin | 18 |
| G | 3 - Procurement | Purchase Order | 21 |
| H | 3 - Procurement | Supplier Stock | 14 |
| I | 3 - Procurement | Distribusi | 10 |
| J | 3 - Procurement | Penerimaan & Retur | 10 |
| K | 4 - Inventory | Stok & Mutasi | 12 |
| L | 5 - Operasional | Order Cucian | 8 |
| M | 5 - Operasional | Permintaan & Distribusi Stok Outlet | 12 |
| N | 5 - Operasional | Jadwal Service & Shift | 12 |
| O | 6 - Franchise | Outlet | 14 |
| P | 6 - Franchise | Loyalti | 10 |
| Q | 7 - Dashboard | Dashboard | 10 |
| | **Total** | | **293** |

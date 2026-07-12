# Test Case — WashHubb

**Modul 1:** Auth & Account Management  
**Modul 2:** Data Master  
**Tanggal:** 13 Juli 2026  
**Total Test Case:** 128

---

## A. Modul 1 — Auth

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_AUTH_001 | Login berhasil dengan email dan password yang valid | Email: admin@washhub.com, Password: password | Sistem menampilkan pesan login berhasil dan memberikan token akses | Pass/Fail |
| TC_AUTH_002 | Login gagal karena email tidak terdaftar di sistem | Email: tidakada@test.com, Password: password | Sistem menampilkan pesan "Kredensial tidak valid" | Pass/Fail |
| TC_AUTH_003 | Login gagal karena password yang dimasukkan salah | Email: admin@washhub.com, Password: salahpassword | Sistem menampilkan pesan "Kredensial tidak valid" | Pass/Fail |
| TC_AUTH_004 | Login gagal karena akun pengguna berstatus nonaktif | Email: user-nonaktif@test.com, Password: password | Sistem menampilkan pesan "Akun Anda nonaktif" | Pass/Fail |
| TC_AUTH_005 | Login gagal karena email tidak diisi | Email: (kosong), Password: password | Sistem menampilkan pesan bahwa email harus diisi | Pass/Fail |
| TC_AUTH_006 | Login gagal karena password tidak diisi | Email: admin@washhub.com, Password: (kosong) | Sistem menampilkan pesan bahwa password harus diisi | Pass/Fail |
| TC_AUTH_007 | Login gagal karena format email tidak valid | Email: bukanemail, Password: password | Sistem menampilkan pesan bahwa format email tidak valid | Pass/Fail |
| TC_AUTH_008 | Login gagal karena email dan password tidak diisi | Email: (kosong), Password: (kosong) | Sistem menampilkan pesan bahwa email dan password harus diisi | Pass/Fail |
| TC_AUTH_009 | Logout berhasil saat pengguna sudah login | Token akses valid | Sistem menampilkan pesan "Logout berhasil" | Pass/Fail |
| TC_AUTH_010 | Logout gagal karena pengguna belum login (tanpa token) | Tidak ada token | Sistem menolak akses (401 Unauthorized) | Pass/Fail |
| TC_AUTH_011 | Menampilkan data profil pengguna yang sedang login | Token akses valid | Sistem menampilkan data pengguna (nama, email, role, permissions) | Pass/Fail |
| TC_AUTH_012 | Menampilkan profil gagal karena tidak memiliki token | Tidak ada token | Sistem menolak akses (401 Unauthorized) | Pass/Fail |
| TC_AUTH_013 | Lupa password — mengecek email yang terdaftar | Email: admin@washhub.com | Sistem menampilkan pesan "Email ditemukan" dan nama pengguna | Pass/Fail |
| TC_AUTH_014 | Lupa password — mengecek email yang tidak terdaftar | Email: tidakada@test.com | Sistem menampilkan pesan "Email tidak ditemukan di sistem" | Pass/Fail |
| TC_AUTH_015 | Lupa password — email tidak diisi | Email: (kosong) | Sistem menampilkan pesan bahwa email harus diisi | Pass/Fail |
| TC_AUTH_016 | Reset password berhasil dengan konfirmasi password yang cocok | Email: admin@washhub.com, Password: passwordbaru123, Konfirmasi: passwordbaru123 | Sistem menampilkan pesan "Password berhasil direset" dan semua token dicabut | Pass/Fail |
| TC_AUTH_017 | Reset password gagal — password baru kurang dari 8 karakter | Email: admin@washhub.com, Password: abc, Konfirmasi: abc | Sistem menampilkan pesan bahwa password minimal 8 karakter | Pass/Fail |
| TC_AUTH_018 | Reset password gagal — konfirmasi password tidak cocok | Email: admin@washhub.com, Password: passwordbaru123, Konfirmasi: berbeda | Sistem menampilkan pesan bahwa konfirmasi password tidak cocok | Pass/Fail |
| TC_AUTH_019 | Reset password gagal — email tidak terdaftar | Email: tidakada@test.com, Password: passwordbaru123 | Sistem menampilkan pesan bahwa email tidak ditemukan | Pass/Fail |

## B. Modul 1 — Account Management (Users & Roles)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_USR_001 | Menampilkan daftar semua pengguna | Login sebagai admin, akses halaman users | Sistem menampilkan daftar pengguna dengan data nama, email, role, dan status | Pass/Fail |
| TC_USR_002 | Menampilkan daftar pengguna dengan pagination | Akses halaman users page 1 | Sistem menampilkan 15 data per halaman | Pass/Fail |
| TC_USR_003 | Menampilkan daftar pengguna gagal karena tidak login | Akses halaman users tanpa token | Sistem menolak akses (401 Unauthorized) | Pass/Fail |
| TC_USR_004 | Menampilkan daftar pengguna gagal karena tidak punya hak akses | Login sebagai user tanpa permission user-list | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass/Fail |
| TC_USR_005 | Menambah pengguna baru berhasil dengan data lengkap | Nama: User Baru, Email: baru@test.com, Password: password123, Role: admin_it | Sistem menyimpan dan menampilkan data pengguna baru | Pass/Fail |
| TC_USR_006 | Menambah pengguna gagal karena email sudah terdaftar | Nama: User Test, Email: admin@washhub.com, Password: password123 | Sistem menampilkan pesan bahwa email sudah digunakan | Pass/Fail |
| TC_USR_007 | Menambah pengguna gagal karena role tidak dipilih | Nama: User Test, Email: test@user.com, Password: password123, Role: (kosong) | Sistem menampilkan pesan bahwa role harus dipilih | Pass/Fail |
| TC_USR_008 | Menambah pengguna gagal karena nama tidak diisi | Nama: (kosong), Email: test@user.com, Password: password123 | Sistem menampilkan pesan bahwa nama harus diisi | Pass/Fail |
| TC_USR_009 | Menambah pengguna gagal karena password kurang dari 8 karakter | Nama: User, Email: test@user.com, Password: abc123 | Sistem menampilkan pesan bahwa password minimal 8 karakter | Pass/Fail |
| TC_USR_010 | Menambah pengguna gagal karena email tidak diisi | Nama: User, Email: (kosong), Password: password123 | Sistem menampilkan pesan bahwa email harus diisi | Pass/Fail |
| TC_USR_011 | Menambah pengguna dengan nomor telepon | Nama: User Telp, Email: telp@user.com, Password: password123, No. Telp: 081234567890 | Sistem menyimpan nomor telepon dengan benar | Pass/Fail |
| TC_USR_012 | Melihat detail pengguna berdasarkan ID | ID user: 1 | Sistem menampilkan detail lengkap pengguna (nama, email, role, status) | Pass/Fail |
| TC_USR_013 | Melihat detail pengguna gagal karena ID tidak ditemukan | ID user: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_USR_014 | Mengubah data pengguna berhasil | ID user: 1, Nama baru: Admin Updated | Sistem menyimpan perubahan dan menampilkan data baru | Pass/Fail |
| TC_USR_015 | Mengubah status pengguna menjadi nonaktif | ID user: 1, Status: nonaktif | Sistem mengubah status pengguna menjadi nonaktif | Pass/Fail |
| TC_USR_016 | Mengubah role pengguna | ID user: 1, Role: franchisor | Sistem mengubah role pengguna | Pass/Fail |
| TC_USR_017 | Mengubah email gagal karena sudah dipakai pengguna lain | ID user: 2, Email: (email milik user 1) | Sistem menampilkan pesan bahwa email sudah digunakan | Pass/Fail |
| TC_USR_018 | Mengubah data pengguna gagal karena ID tidak ditemukan | ID user: 999, Nama: Test | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_USR_019 | Menghapus pengguna berhasil | ID user yang baru dibuat | Sistem menghapus data dan menampilkan pesan sukses | Pass/Fail |
| TC_USR_020 | Menghapus pengguna gagal karena ID tidak ditemukan | ID user: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_USR_021 | Menghapus pengguna — token akses user tersebut tidak bisa dipakai lagi | ID user yang baru dibuat dan sudah login | Token user yang dihapus tidak dapat digunakan lagi | Pass/Fail |
| TC_USR_022 | Menghapus pengguna gagal karena tidak punya hak akses | Login sebagai franchisor | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass/Fail |
| TC_ROLE_001 | Menampilkan daftar semua role | Login sebagai admin, akses halaman roles | Sistem menampilkan daftar role beserta permission-nya | Pass/Fail |
| TC_ROLE_002 | Menampilkan halaman roles gagal karena tidak punya hak akses | Login sebagai manager_outlet | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass/Fail |
| TC_ROLE_003 | Menambah role baru berhasil (tanpa permission) | Kode: test_role, Label: Test Role | Sistem menyimpan role baru | Pass/Fail |
| TC_ROLE_004 | Menambah role baru dengan permission tertentu | Kode: role_po, Label: Role PO, Permissions: po-list, po-create | Sistem menyimpan role dengan permission yang dipilih | Pass/Fail |
| TC_ROLE_005 | Menambah role gagal karena kode sudah ada | Kode: admin_it (sudah terdaftar) | Sistem menampilkan pesan bahwa kode sudah digunakan | Pass/Fail |
| TC_ROLE_006 | Menambah role gagal karena kode tidak diisi | Kode: (kosong), Label: Test | Sistem menampilkan pesan bahwa kode harus diisi | Pass/Fail |
| TC_ROLE_007 | Menambah role gagal karena label tidak diisi | Kode: no_label, Label: (kosong) | Sistem menampilkan pesan bahwa label harus diisi | Pass/Fail |
| TC_ROLE_008 | Melihat detail role beserta permission-nya | ID role: 1 | Sistem menampilkan detail role dan daftar permission | Pass/Fail |
| TC_ROLE_009 | Melihat detail role gagal karena ID tidak ditemukan | ID role: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_ROLE_010 | Mengubah data role berhasil | ID role: 1, Label baru: Admin IT Updated | Sistem menyimpan perubahan dan menampilkan data baru | Pass/Fail |
| TC_ROLE_011 | Mengubah permission pada role | ID role: 1, Permissions: user-list, role-list | Sistem hanya menyimpan permission yang dipilih | Pass/Fail |
| TC_ROLE_012 | Mengubah role gagal karena kode sudah dipakai role lain | ID role: 2, Kode: admin_it | Sistem menampilkan pesan bahwa kode sudah digunakan | Pass/Fail |
| TC_ROLE_013 | Mengubah role gagal karena ID tidak ditemukan | ID role: 999, Label: Test | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_ROLE_014 | Menghapus role berhasil | ID role yang baru dibuat | Sistem menghapus data dan menampilkan pesan sukses | Pass/Fail |
| TC_ROLE_015 | Menghapus role gagal karena ID tidak ditemukan | ID role: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_PERM_001 | Menampilkan daftar semua permission | Login sebagai admin, akses halaman permissions | Sistem menampilkan daftar permission (kode, nama, modul) | Pass/Fail |
| TC_PERM_002 | Melihat detail permission | ID permission: 1 | Sistem menampilkan detail permission | Pass/Fail |
| TC_PERM_003 | Melihat detail permission gagal karena ID tidak ditemukan | ID permission: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_STAT_001 | Menampilkan daftar semua status | Login sebagai admin, akses halaman statuses | Sistem menampilkan daftar status berdasarkan konteks | Pass/Fail |
| TC_STAT_002 | Menampilkan status berdasarkan konteks tertentu | Konteks: akun | Sistem menampilkan status aktif dan nonaktif untuk akun | Pass/Fail |
| TC_STAT_003 | Menampilkan status untuk konteks supplier | Konteks: supplier | Sistem menampilkan status aktif dan nonaktif untuk supplier | Pass/Fail |

## C. Modul 1 — Account Management (Suppliers)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_SUP_001 | Menampilkan daftar semua supplier | Login sebagai admin, akses halaman suppliers | Sistem menampilkan daftar supplier dengan data pengguna dan status | Pass/Fail |
| TC_SUP_002 | Menampilkan halaman suppliers gagal karena tidak punya hak akses | Login sebagai manager_outlet | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass/Fail |
| TC_SUP_003 | Menambah supplier bahan baku berhasil | User: admin, Jenis: bahan_baku, Alamat: Jl. Test No.1, Bahan Baku: [1] | Sistem menyimpan supplier baru dengan jenis bahan baku | Pass/Fail |
| TC_SUP_004 | Menambah supplier mesin berhasil | User: admin, Jenis: mesin, Alamat: Jl. Mesin No.1, Mesin: [1] | Sistem menyimpan supplier baru dengan jenis mesin | Pass/Fail |
| TC_SUP_005 | Menambah supplier tanpa memilih item | User: admin, Jenis: bahan_baku, Alamat: Jl. Baru | Sistem menyimpan supplier dengan daftar item kosong | Pass/Fail |
| TC_SUP_006 | Menambah supplier gagal karena jenis supplier tidak valid | Jenis: invalid | Sistem menampilkan pesan bahwa jenis supplier harus bahan_baku atau mesin | Pass/Fail |
| TC_SUP_007 | Menambah supplier gagal karena user tidak ditemukan | User ID: 999 | Sistem menampilkan pesan bahwa user tidak valid | Pass/Fail |
| TC_SUP_008 | Menambah supplier gagal karena alamat tidak diisi | Alamat: (kosong) | Sistem menampilkan pesan bahwa alamat harus diisi | Pass/Fail |
| TC_SUP_009 | Melihat detail supplier bahan baku | ID supplier bahan baku | Sistem menampilkan detail supplier dan daftar bahan baku yang disuplai | Pass/Fail |
| TC_SUP_010 | Melihat detail supplier gagal karena ID tidak ditemukan | ID supplier: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_SUP_011 | Melihat daftar item yang disuplai oleh supplier bahan baku | ID supplier bahan baku | Sistem menampilkan daftar bahan baku (nama, satuan, harga) | Pass/Fail |
| TC_SUP_012 | Melihat daftar item yang disuplai oleh supplier mesin | ID supplier mesin | Sistem menampilkan daftar mesin (nama, kode mesin, harga) | Pass/Fail |
| TC_SUP_013 | Melihat daftar item dari supplier tanpa item terdaftar | ID supplier tanpa item | Sistem menampilkan daftar kosong | Pass/Fail |
| TC_SUP_014 | Mengubah data supplier berhasil | ID supplier: 1, Alamat baru: Jl. Baru No.5 | Sistem menyimpan perubahan alamat supplier | Pass/Fail |
| TC_SUP_015 | Mengubah daftar item yang disuplai supplier | ID supplier: 1, Bahan Baku ID: [2, 3] | Sistem memperbarui daftar item supplier | Pass/Fail |
| TC_SUP_016 | Mengubah supplier gagal karena ID tidak ditemukan | ID supplier: 999, Alamat: Test | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_SUP_017 | Menghapus supplier berhasil (soft delete) | ID supplier valid | Sistem menghapus data supplier dan menampilkan pesan sukses | Pass/Fail |
| TC_SUP_018 | Menghapus supplier — item-item terlepas dari supplier | ID supplier dengan item terdaftar | Sistem membersihkan relasi item-supplier | Pass/Fail |
| TC_SUP_019 | Menghapus supplier gagal karena ID tidak ditemukan | ID supplier: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_SUP_020 | Memastikan supplier yang sudah dihapus tidak muncul di daftar | ID supplier yang sudah dihapus | Sistem tidak menampilkan supplier tersebut di daftar | Pass/Fail |

## D. Modul 2 — Data Master (Kategori & Bahan Baku)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_CAT_001 | Menampilkan daftar semua kategori | Login sebagai admin, akses halaman kategori | Sistem menampilkan daftar kategori dengan jumlah bahan baku per kategori | Pass/Fail |
| TC_CAT_002 | Menampilkan halaman kategori gagal karena tidak punya hak akses | Login sebagai supplier | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass/Fail |
| TC_CAT_003 | Menambah kategori baru berhasil | Nama: Kategori Test | Sistem menyimpan kategori baru | Pass/Fail |
| TC_CAT_004 | Menambah kategori gagal karena nama sudah ada | Nama: (nama yang sudah terdaftar) | Sistem menampilkan pesan bahwa nama sudah digunakan | Pass/Fail |
| TC_CAT_005 | Menambah kategori gagal karena nama tidak diisi | Nama: (kosong) | Sistem menampilkan pesan bahwa nama harus diisi | Pass/Fail |
| TC_CAT_006 | Melihat detail kategori | ID kategori: 1 | Sistem menampilkan nama kategori dan jumlah bahan baku | Pass/Fail |
| TC_CAT_007 | Melihat detail kategori gagal karena ID tidak ditemukan | ID kategori: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_CAT_008 | Mengubah nama kategori berhasil | ID kategori: 1, Nama: Kategori Updated | Sistem menyimpan perubahan nama kategori | Pass/Fail |
| TC_CAT_009 | Mengubah kategori gagal karena nama sudah dipakai kategori lain | ID kategori: 1, Nama: (nama kategori lain) | Sistem menampilkan pesan bahwa nama sudah digunakan | Pass/Fail |
| TC_CAT_010 | Mengubah kategori gagal karena ID tidak ditemukan | ID kategori: 999, Nama: Test | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_CAT_011 | Menghapus kategori berhasil | ID kategori yang baru dibuat | Sistem menghapus kategori | Pass/Fail |
| TC_CAT_012 | Menghapus kategori — bahan baku di dalamnya ikut terhapus | ID kategori yang memiliki bahan baku | Sistem menghapus kategori beserta bahan baku di dalamnya | Pass/Fail |
| TC_CAT_013 | Menghapus kategori gagal karena ID tidak ditemukan | ID kategori: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_MAT_001 | Menampilkan daftar semua bahan baku | Login sebagai admin, akses halaman materials | Sistem menampilkan daftar bahan baku dengan kategori dan status | Pass/Fail |
| TC_MAT_002 | Menampilkan halaman bahan baku gagal karena tidak punya hak akses | Login sebagai user tanpa permission material-list | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass/Fail |
| TC_MAT_003 | Menambah bahan baku berhasil | Kategori: 1, Nama: Bahan Test, Satuan: kg, Harga: 15000 | Sistem menyimpan bahan baku baru | Pass/Fail |
| TC_MAT_004 | Menambah bahan baku — status otomatis aktif | Data valid tanpa status | Sistem mengisi status aktif secara otomatis | Pass/Fail |
| TC_MAT_005 | Menambah bahan baku gagal karena kategori tidak valid | ID kategori: 999 | Sistem menampilkan pesan bahwa kategori tidak valid | Pass/Fail |
| TC_MAT_006 | Menambah bahan baku gagal karena nama tidak diisi | Nama: (kosong) | Sistem menampilkan pesan bahwa nama harus diisi | Pass/Fail |
| TC_MAT_007 | Menambah bahan baku gagal karena satuan tidak diisi | Satuan: (kosong) | Sistem menampilkan pesan bahwa satuan harus diisi | Pass/Fail |
| TC_MAT_008 | Menambah bahan baku gagal karena harga negatif | Harga: -1000 | Sistem menampilkan pesan bahwa harga tidak boleh negatif | Pass/Fail |
| TC_MAT_009 | Melihat detail bahan baku | ID bahan baku: 1 | Sistem menampilkan detail (nama, kategori, satuan, harga, status) | Pass/Fail |
| TC_MAT_010 | Melihat detail bahan baku gagal karena ID tidak ditemukan | ID bahan baku: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_MAT_011 | Mengubah data bahan baku berhasil | ID bahan baku: 1, Nama: Bahan Updated | Sistem menyimpan perubahan | Pass/Fail |
| TC_MAT_012 | Mengubah kategori bahan baku | ID bahan baku: 1, Kategori: 2 | Sistem mengubah kategori bahan baku | Pass/Fail |
| TC_MAT_013 | Mengubah harga standar bahan baku | ID bahan baku: 1, Harga: 25000 | Sistem mengubah harga standar | Pass/Fail |
| TC_MAT_014 | Mengubah bahan baku gagal karena kategori tidak valid | ID bahan baku: 1, Kategori: 999 | Sistem menampilkan pesan bahwa kategori tidak valid | Pass/Fail |
| TC_MAT_015 | Mengubah bahan baku gagal karena ID tidak ditemukan | ID bahan baku: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_MAT_016 | Menghapus bahan baku berhasil | ID bahan baku valid | Sistem menghapus data bahan baku | Pass/Fail |
| TC_MAT_017 | Menghapus bahan baku gagal karena ID tidak ditemukan | ID bahan baku: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |

## E. Modul 2 — Data Master (Jenis Layanan)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_SVC_001 | Menampilkan daftar semua jenis layanan | Login sebagai admin, akses halaman services | Sistem menampilkan daftar layanan dengan material dan status | Pass/Fail |
| TC_SVC_002 | Menampilkan halaman layanan gagal karena tidak punya hak akses | Login sebagai supplier | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass/Fail |
| TC_SVC_003 | Menambah jenis layanan baru berhasil | Nama: Layanan Test, Harga per Kg: 50000 | Sistem menyimpan jenis layanan baru | Pass/Fail |
| TC_SVC_004 | Menambah layanan — status otomatis aktif | Data valid tanpa status | Sistem mengisi status aktif secara otomatis | Pass/Fail |
| TC_SVC_005 | Menambah layanan gagal karena nama sudah ada | Nama: (nama yang sudah terdaftar) | Sistem menampilkan pesan bahwa nama sudah digunakan | Pass/Fail |
| TC_SVC_006 | Menambah layanan gagal karena harga negatif | Harga per Kg: -5000 | Sistem menampilkan pesan bahwa harga tidak boleh negatif | Pass/Fail |
| TC_SVC_007 | Menambah layanan gagal karena nama tidak diisi | Nama: (kosong) | Sistem menampilkan pesan bahwa nama harus diisi | Pass/Fail |
| TC_SVC_008 | Melihat detail jenis layanan | ID layanan: 1 | Sistem menampilkan detail layanan (nama, harga, status, material) | Pass/Fail |
| TC_SVC_009 | Melihat detail layanan gagal karena ID tidak ditemukan | ID layanan: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_SVC_010 | Mengubah data layanan berhasil | ID layanan: 1, Nama: Layanan Updated | Sistem menyimpan perubahan | Pass/Fail |
| TC_SVC_011 | Mengubah harga layanan | ID layanan: 1, Harga per Kg: 75000 | Sistem mengubah harga layanan | Pass/Fail |
| TC_SVC_012 | Mengubah layanan gagal karena ID tidak ditemukan | ID layanan: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_SVC_013 | Menghapus layanan berhasil | ID layanan valid | Sistem menghapus data layanan | Pass/Fail |
| TC_SVC_014 | Menghapus layanan gagal karena ID tidak ditemukan | ID layanan: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_SVC_015 | Menambahkan material ke layanan berhasil | ID layanan: 1, Material ID: 1, Jumlah Konsumsi: 0.5 | Sistem menambahkan material ke layanan | Pass/Fail |
| TC_SVC_016 | Menambahkan material gagal karena material tidak ditemukan | ID layanan: 1, Material ID: 999 | Sistem menampilkan pesan bahwa material tidak valid | Pass/Fail |
| TC_SVC_017 | Menambahkan material gagal karena jumlah konsumsi 0 | ID layanan: 1, Material ID: 1, Jumlah: 0 | Sistem menampilkan pesan bahwa jumlah konsumsi harus lebih dari 0 | Pass/Fail |
| TC_SVC_018 | Menambahkan material yang sama ke layanan (duplikat) | ID layanan: 1, Material ID: 1 (sama) | Sistem tidak membuat duplikat, data tetap aman | Pass/Fail |
| TC_SVC_019 | Menghapus material dari layanan berhasil | ID layanan: 1, Material ID: 1 | Sistem menghapus material dari layanan | Pass/Fail |
| TC_SVC_020 | Menghapus material yang tidak terdaftar di layanan | ID layanan: 1, Material ID: 999 | Sistem tetap berhasil (tidak error) | Pass/Fail |

## F. Modul 2 — Data Master (Mesin)

| Test Case ID | Skenario Pengujian | Input | Expected Output | Status |
|---|---|---|---|---|
| TC_MCH_001 | Menampilkan daftar semua mesin | Login sebagai admin, akses halaman machines | Sistem menampilkan daftar mesin dengan status | Pass/Fail |
| TC_MCH_002 | Menampilkan halaman mesin gagal karena tidak punya hak akses | Login sebagai supplier | Sistem menampilkan pesan tidak punya akses (403 Forbidden) | Pass/Fail |
| TC_MCH_003 | Menambah mesin baru berhasil (minimal) | Nama: Mesin Test, Kode Mesin: MCH-001 | Sistem menyimpan mesin baru | Pass/Fail |
| TC_MCH_004 | Menambah mesin dengan data lengkap | Nama: Mesin Lengkap, Kode: MCH-002, Merk: MerkX, Tipe: TipeY, Kapasitas: 100, Harga: 5000000 | Sistem menyimpan semua data dengan benar | Pass/Fail |
| TC_MCH_005 | Menambah mesin — status otomatis aktif | Data valid tanpa status | Sistem mengisi status aktif secara otomatis | Pass/Fail |
| TC_MCH_006 | Menambah mesin gagal karena kode mesin sudah ada | Kode Mesin: MCH-001 (sudah terdaftar) | Sistem menampilkan pesan bahwa kode mesin sudah digunakan | Pass/Fail |
| TC_MCH_007 | Menambah mesin gagal karena nama tidak diisi | Nama: (kosong), Kode: MCH-010 | Sistem menampilkan pesan bahwa nama harus diisi | Pass/Fail |
| TC_MCH_008 | Menambah mesin gagal karena kode mesin tidak diisi | Nama: Mesin, Kode: (kosong) | Sistem menampilkan pesan bahwa kode mesin harus diisi | Pass/Fail |
| TC_MCH_009 | Menambah mesin gagal karena kapasitas negatif | Kapasitas: -5 | Sistem menampilkan pesan bahwa kapasitas tidak boleh negatif | Pass/Fail |
| TC_MCH_010 | Melihat detail mesin | ID mesin: 1 | Sistem menampilkan detail mesin (nama, kode, merk, tipe, kapasitas, harga, status) | Pass/Fail |
| TC_MCH_011 | Melihat detail mesin gagal karena ID tidak ditemukan | ID mesin: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_MCH_012 | Mengubah data mesin berhasil | ID mesin: 1, Nama: Mesin Updated | Sistem menyimpan perubahan | Pass/Fail |
| TC_MCH_013 | Mengubah seluruh data mesin | ID mesin: 1, Nama: Baru, Merk: MerkBaru, Tipe: TipeBaru, Kapasitas: 200, Harga: 10000000 | Sistem mengubah semua data mesin | Pass/Fail |
| TC_MCH_014 | Mengubah mesin gagal karena kode mesin sudah dipakai mesin lain | ID mesin: 1, Kode: (kode mesin lain) | Sistem menampilkan pesan bahwa kode mesin sudah digunakan | Pass/Fail |
| TC_MCH_015 | Mengubah mesin gagal karena ID tidak ditemukan | ID mesin: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_MCH_016 | Menghapus mesin berhasil | ID mesin valid | Sistem menghapus data mesin | Pass/Fail |
| TC_MCH_017 | Menghapus mesin gagal karena ID tidak ditemukan | ID mesin: 999 | Sistem menampilkan pesan data tidak ditemukan (404) | Pass/Fail |
| TC_MCH_018 | Memastikan mesin yang sudah dihapus tidak muncul di daftar | ID mesin yang sudah dihapus | Sistem tidak menampilkan mesin tersebut di daftar | Pass/Fail |

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
| | **Total** | | **150** |

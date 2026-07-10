# PRD Backend API WashHub (Laravel 13)

## 1. Overview
Sistem backend REST API untuk manajemen franchise laundry WashHub menggunakan Laravel 13, MySQL, Sanctum Authentication, dan Spatie Permission.

## 2. Scope Modul
- Modul 1: Account & Supplier Management
- Modul 2: Data Master
- Modul 3: Procurement
- Modul 4: Inventory

---

# Modul 1 Account & Supplier

## Flow
```mermaid
flowchart TD
A[Admin IT Login] --> B[Create User]
B --> C[Assign Role Spatie]
C --> D[User Aktif]
D --> E[Create Supplier Profile]
E --> F[Pilih Jenis Supplier]
F --> G[Supplier Bahan Baku / Mesin]
```

## ERD
```mermaid
erDiagram
users ||--o{ supplier : memiliki
users ||--o{ model_has_roles : memiliki
roles ||--o{ model_has_roles : memiliki
roles ||--o{ role_has_permissions : memiliki
permissions ||--o{ role_has_permissions : memiliki

users {
id bigint PK
name varchar
email varchar
password varchar
status_id bigint
}

supplier {
id bigint PK
user_id bigint FK
jenis_supplier enum
alamat text
status_id bigint
}
```

---

# Modul 2 Data Master

## Flow
```mermaid
flowchart TD
A[Franchisor] --> B[Kelola Kategori]
B --> C[Kelola Bahan Baku]
C --> D[Kelola Jenis Layanan]
D --> E[Mapping Konsumsi Bahan]
A --> F[Kelola Mesin]
```

## ERD
```mermaid
erDiagram
kategori_bahan_baku ||--o{ bahan_baku : memiliki
jenis_layanan ||--o{ jenis_layanan_bahan_baku : memiliki
bahan_baku ||--o{ jenis_layanan_bahan_baku : digunakan

kategori_bahan_baku {
id bigint PK
nama varchar
}

bahan_baku {
id bigint PK
kategori_id bigint FK
nama varchar
satuan varchar
harga_standar decimal
}

jenis_layanan {
id bigint PK
nama varchar
harga_standar_per_kg decimal
}

mesin {
id bigint PK
nama varchar
kode_mesin varchar
merk varchar
tipe varchar
kapasitas int
}
```

---

# Modul 3 Procurement

## Flow
```mermaid
flowchart TD
A[Tim Pengadaan] --> B[Buat PO]
B --> C[Pilih Jenis PO]
C --> D[Filter Supplier]
D --> E[Tambah Item]
E --> F[Kirim Supplier]
F --> G[Supplier Validasi]
G --> H[Distribusi Barang]
H --> I[Penerimaan]
I --> J[Update Stock]
```

## ERD
```mermaid
erDiagram
supplier ||--o{ purchase_order : menerima
purchase_order ||--o{ purchase_order_item : memiliki
purchase_order ||--o{ distribusi_barang : menghasilkan
distribusi_barang ||--o{ penerimaan_barang : memiliki

purchase_order {
id bigint PK
nomor_po varchar
supplier_id bigint FK
jenis_po enum
status_id bigint
}

purchase_order_item {
id bigint PK
po_id bigint FK
item_id bigint
jumlah decimal
harga decimal
}

distribusi_barang {
id bigint PK
po_id bigint FK
nomor_distribusi varchar
status_id bigint
}

penerimaan_barang {
id bigint PK
distribusi_barang_id bigint FK
tanggal_terima date
total_bayar decimal
}
```

---

# Modul 4 Inventory

## Flow
```mermaid
flowchart TD
A[Penerimaan Barang] --> B[Tambah Stock Pusat]
B --> C[Buat Mutasi]
C --> D[Monitoring Stock]
D --> E[Laporan Inventory]
```

## ERD
```mermaid
erDiagram
bahan_baku ||--|| stok_pusat_bahan_baku : memiliki
stok_pusat_bahan_baku ||--o{ mutasi_stok : mencatat

mesin ||--|| stok_pusat_mesin : memiliki

stok_pusat_bahan_baku {
id bigint PK
bahan_baku_id bigint FK
stok_saat_ini decimal
}

mutasi_stok {
id bigint PK
stok_id bigint FK
jenis_mutasi varchar
jumlah decimal
tanggal datetime
}

stok_pusat_mesin {
id bigint PK
mesin_id bigint FK
stok_saat_ini int
}
```

---

# Permission Matrix

| Role | Hak Akses |
|-|-|
| admin_it | user, role, permission |
| franchisor | master data |
| procurement | supplier, PO |
| supplier | validasi PO |
| manager_outlet | inventory outlet |

---

# API Structure

```
/api/auth
/api/users
/api/roles
/api/permissions

/api/suppliers

/api/master/categories
/api/master/materials
/api/master/services
/api/master/machines

/api/procurement/purchase-orders
/api/procurement/distributions
/api/procurement/receipts

/api/inventory/stocks
/api/inventory/mutations
```

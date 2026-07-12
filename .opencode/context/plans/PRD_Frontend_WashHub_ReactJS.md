# PRD Frontend WashHub (React JS)

## 1. Overview

**Project**: Frontend Web App untuk manajemen franchise laundry WashHub
**Tech Stack**: React JS (.js), Vite, Tailwind CSS, Axios, React Router v6
**Backend**: Laravel REST API (http://localhost:8000/api)
**Target**: React build di port 5173 (dev), production di domain terpisah

**Prinsip**:
- Semua file `.js` — tidak pakai TypeScript
- Form Create/Edit harus **rapi, proper, profesional** — grid 2 kolom, label di atas input, spacing konsisten, error message inline
- Layout dengan sidebar navigasi per modul
- View bisa disesuaikan dengan gambar desain yang akan dikirim nanti

---

## 2. Non-Goals (Explicit)

- Tidak login/register publik — hanya admin_it yang buat user
- Tidak real-time / websocket
- Tidak upload file (belum)
- Tidak dark mode (belum)
- Tidak i18n

---

## 3. Tech Stack

| Layer | Pilihan | Alasan |
|-------|---------|--------|
| Build tool | **Vite** | Cepat, standar React modern |
| UI Library | **React 18** | Sesuai permintaan |
| Bahasa | **JS (.js)** | No TypeScript |
| Routing | **React Router v6** | Standar |
| HTTP | **Axios** | Interceptor untuk token, error handling |
| Styling | **Tailwind CSS v3** | Form rapi, responsive, utility-first |
| State auth | **Context API** | Ringan, no extra deps |
| Notification | **react-hot-toast** | Ringan, toast notif |

---

## 4. Full Route Map

### 4.1 Auth

| Route | Page | Backend Endpoint |
|-------|------|------------------|
| `/login` | LoginPage | POST `/api/auth/login` |
| (protected) | Layout + Sidebar | GET `/api/auth/me` |

### 4.2 Module 1 — Account & Supplier

| Route | Page | API |
|-------|------|-----|
| `/users` | UserList | GET `/api/users` |
| `/users/create` | UserForm (create) | POST `/api/users` |
| `/users/:id/edit` | UserForm (edit) | GET+PUT `/api/users/{id}` |
| `/roles` | RoleList | GET `/api/roles` |
| `/roles/create` | RoleForm | POST `/api/roles` |
| `/roles/:id/edit` | RoleForm | GET+PUT `/api/roles/{id}` |
| `/suppliers` | SupplierList | GET `/api/suppliers` |
| `/suppliers/create` | SupplierForm | POST `/api/suppliers` |
| `/suppliers/:id/edit` | SupplierForm | GET+PUT `/api/suppliers/{id}` |

### 4.3 Module 2 — Data Master

| Route | Page | API |
|-------|------|-----|
| `/master/categories` | CategoryList | GET `/api/master/categories` |
| `/master/categories/create` | CategoryForm | POST |
| `/master/categories/:id/edit` | CategoryForm | GET+PUT |
| `/master/materials` | MaterialList | GET `/api/master/materials` |
| `/master/materials/create` | MaterialForm | POST |
| `/master/materials/:id/edit` | MaterialForm | GET+PUT |
| `/master/services` | ServiceList | GET `/api/master/services` |
| `/master/services/create` | ServiceForm | POST |
| `/master/services/:id/edit` | ServiceForm | GET+PUT |
| `/master/services/:id` | ServiceDetail (manage material mapping) | GET + POST attach / DELETE detach |
| `/master/machines` | MachineList | GET `/api/master/machines` |
| `/master/machines/create` | MachineForm | POST |
| `/master/machines/:id/edit` | MachineForm | GET+PUT |

### 4.4 Module 3 — Procurement

| Route | Page | API |
|-------|------|-----|
| `/procurement/purchase-orders` | POList | GET `/api/procurement/purchase-orders` |
| `/procurement/purchase-orders/create` | POForm (with items array) | POST |
| `/procurement/purchase-orders/:id/edit` | POForm | GET+PUT |
| `/procurement/purchase-orders/:id` | PODetail | GET |
| `/procurement/distributions` | DistributionList | GET |
| `/procurement/distributions/create` | DistributionForm | POST |
| `/procurement/distributions/:id` | DistributionDetail | GET + penerimaan list |
| `/procurement/receipts` | ReceiptList | GET |
| `/procurement/receipts/create` | ReceiptForm | POST |
| `/procurement/receipts/:id` | ReceiptDetail | GET |

### 4.5 Module 4 — Inventory

| Route | Page | API |
|-------|------|-----|
| `/inventory/stocks` | StockList (with type filter) | GET `/api/inventory/stocks?type=bahan\|mesin` |
| `/inventory/stocks/:type/:id` | StockDetail | GET `/api/inventory/stocks/{type}/{id}` |
| `/inventory/mutations` | MutationList | GET `/api/inventory/mutations` |
| `/inventory/mutations/create` | MutationForm | POST |

---

## 5. Component Architecture

```
src/
├── api/
│   ├── axios.js              # Axios instance + interceptor
│   ├── auth.js
│   ├── users.js
│   ├── roles.js
│   ├── permissions.js
│   ├── suppliers.js
│   ├── categories.js
│   ├── materials.js
│   ├── services.js
│   ├── machines.js
│   ├── purchaseOrders.js
│   ├── distributions.js
│   ├── receipts.js
│   ├── stocks.js
│   └── mutations.js
│
├── components/
│   ├── layout/
│   │   ├── MainLayout.jsx
│   │   ├── Sidebar.jsx
│   │   └── Header.jsx
│   │
│   ├── ui/                   # Reusable clean UI
│   │   ├── FormInput.jsx
│   │   ├── FormSelect.jsx
│   │   ├── FormTextarea.jsx
│   │   ├── FormGrid.jsx
│   │   ├── FormActions.jsx
│   │   ├── PageHeader.jsx
│   │   ├── DataTable.jsx
│   │   ├── Pagination.jsx
│   │   ├── StatusBadge.jsx
│   │   ├── ConfirmModal.jsx
│   │   ├── LoadingSpinner.jsx
│   │   └── EmptyState.jsx
│   │
│   └── forms/
│       └── index.js
│
├── context/
│   └── AuthContext.js
│
├── hooks/
│   ├── useApi.js
│   └── useAuth.js
│
├── pages/
│   ├── auth/
│   │   └── LoginPage.jsx
│   ├── users/
│   │   ├── UserList.jsx
│   │   └── UserForm.jsx
│   ├── roles/
│   │   ├── RoleList.jsx
│   │   └── RoleForm.jsx
│   ├── suppliers/
│   │   ├── SupplierList.jsx
│   │   └── SupplierForm.jsx
│   ├── master/
│   │   ├── categories/
│   │   │   ├── CategoryList.jsx
│   │   │   └── CategoryForm.jsx
│   │   ├── materials/
│   │   │   ├── MaterialList.jsx
│   │   │   └── MaterialForm.jsx
│   │   ├── services/
│   │   │   ├── ServiceList.jsx
│   │   │   ├── ServiceForm.jsx
│   │   │   └── ServiceDetail.jsx
│   │   └── machines/
│   │       ├── MachineList.jsx
│   │       └── MachineForm.jsx
│   ├── procurement/
│   │   ├── PurchaseOrderList.jsx
│   │   ├── PurchaseOrderForm.jsx
│   │   ├── PurchaseOrderDetail.jsx
│   │   ├── DistributionList.jsx
│   │   ├── DistributionForm.jsx
│   │   ├── DistributionDetail.jsx
│   │   ├── ReceiptList.jsx
│   │   ├── ReceiptForm.jsx
│   │   └── ReceiptDetail.jsx
│   └── inventory/
│       ├── StockList.jsx
│       ├── StockDetail.jsx
│       ├── MutationList.jsx
│       └── MutationForm.jsx
│
├── utils/
│   ├── format.js
│   └── constants.js
│
├── App.jsx
├── main.jsx
└── index.css
```

---

## 6. Clean Form Standard (Critical)

Setiap form Create/Edit harus mengikuti standar ini:

```
┌──────────────────────────────────────┐
│  PageHeader (Title + Breadcrumb)      │
├──────────────────────────────────────┤
│  ┌─ FormGrid (2 columns) ──────────┐  │
│  │  ┌─ FormInput ──┐ ┌─ FormSelect ─┐│  │
│  │  │ Label        │ │ Label        ││  │
│  │  │ [input]      │ │ [select]     ││  │
│  │  │ error msg    │ │ error msg    ││  │
│  │  └──────────────┘ └──────────────┘│  │
│  │  ┌─ FormInput ──┐ ┌─ FormInput ──┐│  │
│  │  │ Label        │ │ Label        ││  │
│  │  │ [input]      │ │ [input]      ││  │
│  │  └──────────────┘ └──────────────┘│  │
│  └───────────────────────────────────┘  │
│                                         │
│  ┌─ FormActions ─────────────────────┐  │
│  │  [Cancel]         [Save]          │  │
│  └───────────────────────────────────┘  │
└──────────────────────────────────────┘
```

Aturan:
- Grid 2 kolom untuk form field (1 kolom untuk field panjang seperti alamat)
- Label di atas input (bukan di samping)
- Input full-width dalam kolomnya
- Error message merah kecil di bawah input
- Tombol Submit warna solid, Cancel outline
- Required field ditandai asterisk merah
- Spacing 24px antar baris
- Padding card 24px
- Background putih, border subtle

---

## 7. API Client Pattern

**axios.js**:
- Base URL: `http://localhost:8000/api` (dev) / production URL
- Request interceptor: attach `Authorization: Bearer {token}` dari localStorage
- Response interceptor: if 401 → clear token → redirect `/login`

**Per-module API file** (contoh `users.js`):
```js
import api from './axios';

export const getUsers = (params) => api.get('/users', { params });
export const getUser = (id) => api.get(`/users/${id}`);
export const createUser = (data) => api.post('/users', data);
export const updateUser = (id, data) => api.put(`/users/${id}`, data);
export const deleteUser = (id) => api.delete(`/users/${id}`);
```

---

## 8. Auth Flow

1. User login → POST `/api/auth/login` → dapat `token` + `user` (roles + permissions)
2. Simpan token di `localStorage` + set state `AuthContext`
3. Axios interceptor inject `Bearer token` ke semua request
4. `MainLayout.jsx` cek `AuthContext` — kalau tidak ada → redirect `/login`
5. Logout → POST `/api/auth/logout` → clear storage → redirect `/login`

**Sidebar menu visibility** berdasarkan permission user:
- `admin_it`: Users, Roles, Permissions, Suppliers
- `franchisor`: Categories, Materials, Services, Machines
- `procurement`: Suppliers, PO, Distributions, Receipts
- `supplier`: PO (hanya PO miliknya + validate)
- `manager_outlet`: Stocks, Mutations

---

## 9. Reusable UI Components (Tailwind)

| Komponen | Style |
|----------|-------|
| **DataTable** | White bg, striped rows, sticky header, sort arrows, hover effect, action column |
| **Pagination** | Prev/next + page numbers, disabled state, active highlight |
| **StatusBadge** | Hijau=aktif/disetujui, Merah=nonaktif/ditolak, Kuning=draft/pending, Abu-abu=default |
| **ConfirmModal** | Overlay blur, centered white card, red delete button, cancel outline |
| **PageHeader** | Large title + breadcrumb + "Tambah Baru" button (kanan) |
| **FormInput** | Label + required asterisk (*) merah, input border-gray, focus:ring-blue, error message red |
| **FormSelect** | Same style, dropdown options |
| **FormTextarea** | Same style, resizable vertical |
| **FormGrid** | 2-column grid wrapper, gap-6 |
| **FormActions** | Cancel (outline) left, Submit (solid) right |
| **LoadingSpinner** | Centered spinner SVG animasi |
| **EmptyState** | Icon + "Belum ada data" text |
| **Card** | White bg, rounded-xl, border, shadow-sm, p-6 |

---

## 10. DataTable Column Standards

| Modul | Columns |
|-------|---------|
| Users | Nama, Email, Role, Status, Dibuat, Aksi |
| Roles | Nama, Jumlah Permission, Aksi |
| Suppliers | Nama User, Jenis Supplier, Alamat, Status, Aksi |
| Categories | Nama, Jumlah Bahan Baku, Aksi |
| Materials | Nama, Kategori, Satuan, Harga Standar, Aksi |
| Services | Nama, Harga/Kg, Aksi |
| Machines | Nama, Kode Mesin, Merk, Tipe, Kapasitas, Aksi |
| PO | Nomor PO, Supplier, Jenis PO, Status, Validasi, Aksi |
| Distributions | Nomor Distribusi, Nomor PO, Status, Aksi |
| Receipts | Distribusi, Tanggal Terima, Total Bayar, Aksi |
| Stocks | Item, Tipe, Stok Saat Ini, Terakhir Update |
| Mutations | Item, Jenis Mutasi, Jumlah, Tanggal, Keterangan |

---

## 11. Delivery Order

Semua modul dikerjakan dalam 1 siklus:

```
Phase 1: Project Setup (Vite + Tailwind + Router + Axios + base components)
Phase 2: Auth + Layout (LoginPage, MainLayout, Sidebar, Header)
Phase 3: Module 1 — Account & Supplier (7 pages)
Phase 4: Module 2 — Data Master (11 pages)
Phase 5: Module 3 — Procurement (9 pages)
Phase 6: Module 4 — Inventory (4 pages)
Phase 7: Refine layout sesuai gambar desain yang akan dikirim
```

---

## 12. Dependencies

```json
{
  "dependencies": {
    "react": "^18",
    "react-dom": "^18",
    "react-router-dom": "^6",
    "axios": "^1",
    "react-hot-toast": "^2"
  },
  "devDependencies": {
    "vite": "^5",
    "@vitejs/plugin-react": "^4",
    "tailwindcss": "^3",
    "postcss": "^8",
    "autoprefixer": "^10"
  }
}
```

---

## 13. Effort Estimation

| Phase | Pages | Components | API Modules | Est. Time |
|-------|-------|------------|-------------|-----------|
| Setup | 1 | 4 | 3 | 2h |
| Auth + Layout | 2 | 3 | 1 | 1.5h |
| Module 1 | 7 | 2 | 3 | 4h |
| Module 2 | 11 | 2 | 4 | 5h |
| Module 3 | 9 | 3 | 3 | 6h |
| Module 4 | 4 | 1 | 2 | 2.5h |
| **Total** | **34** | **15** | **16** | **~21h** |

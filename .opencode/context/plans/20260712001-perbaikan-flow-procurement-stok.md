# Simple Plan: Perbaikan Flow Pengadaan Barang & Stok (Modul 3 & 4)

**ID**: 20260712001
**Status**: complete
**Created**: 2026-07-12
**Scope**: moderate

---

## Goal

Mengubah alur Modul 3 (Pengadaan) dan Modul 4 (Stok) agar mengikuti PRD baru: PO status driven by Distribusi/Retur (bukan manual), hapus validasi supplier, tambah status diterima/diterima_sebagian, Distribusi punya aksi Tolak/Kirim/Kirim Sebagian/Diterima, Retur auto-update PO, Mutasi masuk wajib referensi PO, dan hapus input manual stok pusat.

## Context

**Current**: PO flow has manual "Kirim" button, supplier validation step (per-item approve/reject), no `diterima`/`diterima_sebagian` statuses, no `catatan_status`/`qty_diterima` fields, manual stock mutations allowed.
**Target**: PO status purely driven by Distribusi/Retur triggers, no manual status changes, proper end-to-end flow per new PRD.

## Non-Goals

- Stok outlet (out of scope per both PRDs)
- Multi-level approval flow
- WhatsApp/email notifications
- Refactoring unrelated modules (1, 2)

---

## Tasks

### 1. Database Migration: Add columns & statuses
- **Accept**:
  - `purchase_orders` gets `catatan_status` (text, nullable)
  - `purchase_order_item_bahan_baku` gets `qty_diterima` (decimal, nullable)
  - `purchase_order_item_mesin` gets `qty_diterima` (int, nullable)
  - `distribusi_barang` gets `catatan` (text, nullable)
  - `mutasi_stok_pusat_bahan_baku` gets `referensi_po_id` (bigint, nullable, FK → purchase_orders)
  - `mutasi_stok_pusat_mesin` gets `referensi_po_id` (bigint, nullable, FK → purchase_orders)
  - New statuses seeded: `purchase_order → diterima`, `purchase_order → diterima_sebagian`
- **Files**: `database/migrations/2026_07_12_000001_add_prd_flow_columns.php`, `database/seeders/StatusSeeder.php`

### 2. Backend: Update PO Model & Controller
- **Accept**:
  - PurchaseOrder model: add `catatan_status` to fillable
  - PurchaseOrderItem models: add `qty_diterima` to fillable
  - Remove `kirim()` method from PurchaseOrderController
  - Remove `validateItem()` and `validatePo()` methods
  - Remove related routes from `api.php`
  - Add `selesai()` method (Tim Pengadaan marks PO complete after physical check)
  - Add `diterimaSebagian()` method (Tim Pengadaan marks partial receipt)
- **Files**: `app/Models/PurchaseOrder.php`, `app/Models/PurchaseOrderItemBahanBaku.php`, `app/Models/PurchaseOrderItemMesin.php`, `app/Http/Controllers/Api/Procurement/PurchaseOrderController.php`, `routes/api.php`

### 3. Backend: Update Distribusi Controller
- **Accept**:
  - DistribusiBarang model: add `catatan` to fillable
  - DistribusiController store: accept `catatan`, start with `menunggu` status (not `dikirim`)
  - Add `tolak()` method: mandatory `catatan`, set Distribusi `ditolak`, auto-update PO `ditolak`
  - Add `kirimSebagian()` method: mandatory qty per item + `catatan`, set Distribusi `dikirim_sebagian`, auto-update PO `dikirim_sebagian`
  - Add `kirim()` method: set Distribusi `dikirim`, auto-update PO `dikirim`
  - Add `diterima()` method: Tim Pengadaan marks Distribusi `diterima`, auto-update PO `diterima`
  - Each status change triggers corresponding PO status update atomically
  - Stock decrease on `dikirim`/`dikirim_sebagian` (existing logic, verify)
- **Files**: `app/Models/DistribusiBarang.php`, `app/Http/Controllers/Api/Procurement/DistribusiController.php`, `routes/api.php`

### 4. Backend: Update Penerimaan Controller
- **Accept**:
  - PenerimaanController store: update PO items with `qty_diterima` per item
  - When all items received fully and conditions are good → PO `selesai` (via `checkPoSelesai`)
  - When some items missing/defective → PO `diterima_sebagian`
  - Keep existing stock update logic for kondisi baik
  - Keep existing auto-retur creation for kondisi cacat
- **Files**: `app/Http/Controllers/Api/Procurement/PenerimaanController.php`

### 5. Backend: Update Retur Controller
- **Accept**:
  - ReturController `confirm()` selesai: auto-update PO to `selesai` (atomic with Retur status change)
  - Add `checkPoSelesai()` logic (shared with PenerimaanController, extract to service or trait)
  - Add PO search endpoint for Retur: returns PO with `qty_diterima` and qty kurang per item
- **Files**: `app/Http/Controllers/Api/Procurement/ReturController.php`, `routes/api.php`

### 6. Backend: Update Mutasi & Stock Controllers
- **Accept**:
  - MutasiController `store()`: remove `penyesuaian` from valid types, require `referensi_po_id` for `masuk` type
  - Validate PO status is `selesai` before allowing `masuk` mutation
  - Auto-fill items & qty from PO when `masuk` with PO reference
  - StockController: remove `store()` method (no manual stok pusat input)
  - Remove related route `POST /inventory/stocks`
- **Files**: `app/Http/Controllers/Api/Inventory/MutasiController.php`, `app/Http/Controllers/Api/Inventory/StockController.php`, `routes/api.php`

### 7. Frontend: Update PO Pages
- **Accept**:
  - PurchaseOrderList: remove "Kirim" button, remove "Edit" button for non-diajukan
  - PurchaseOrderDetail: remove supplier validation UI (Setuju/Sebagian/Tolak buttons), show `catatan_status`, show `qty_diterima` per item, add "Disetujui" and "Diterima Sebagian" buttons for Tim Pengadaan
  - PurchaseOrderForm: keep as-is (only for diajukan status)
  - Add `kirim` API call in purchaseOrders.js (for the distribute step, not PO manual kirim)
- **Files**: `frontend/src/pages/procurement/PurchaseOrderList.jsx`, `frontend/src/pages/procurement/PurchaseOrderDetail.jsx`, `frontend/src/api/purchaseOrders.js`

### 8. Frontend: Update Distribusi Pages
- **Accept**:
  - DistributionForm: PO filter shows `diajukan` (not `disetujui`), add `catatan` field
  - DistributionDetail: show `catatan`, add action buttons for Tolak/Kirim/Kirim Sebagian/Diterima based on status
  - Add API calls for new Distribusi actions
- **Files**: `frontend/src/pages/procurement/DistributionForm.jsx`, `frontend/src/pages/procurement/DistributionDetail.jsx`, `frontend/src/api/distributions.js`

### 9. Frontend: Update Retur & Mutasi Pages
- **Accept**:
  - ReturnList: add ability to search PO for retur creation, show qty diterima vs qty kurang
  - MutationForm: remove `penyesuaian` option, add PO reference selector for `masuk` type, auto-fill items & qty
  - MutationList: update column display
  - Remove manual stock creation route from frontend
  - Update `constants.js`: remove `penyesuaian` from `JENIS_MUTASI_OPTIONS`
- **Files**: `frontend/src/pages/procurement/ReturnList.jsx`, `frontend/src/pages/inventory/MutationForm.jsx`, `frontend/src/pages/inventory/MutationList.jsx`, `frontend/src/utils/constants.js`, `frontend/src/api/mutations.js`

### 10. Backend: Extract shared PO status check logic
- **Accept**:
  - Extract `checkPoSelesai()` to `StockService` or a dedicated `PoStatusService`
  - Ensure atomic transaction with status changes
  - Single source of truth for PO completion logic
- **Files**: `app/Services/StockService.php` (or new service)

---

## Verify

- `cd backend && php artisan migrate --pretend` (migration syntax check)
- `cd backend && php artisan route:list --path=procurement` (verify routes)
- `cd backend && php artisan route:list --path=inventory` (verify routes)
- Manual review: PO status transitions match PRD state machine
- Manual review: No manual PO status change endpoints remain

# Progress: Perbaikan Flow Pengadaan Barang & Stok (Modul 3 & 4)

**Plan**: .opencode/context/plans/20260712001-perbaikan-flow-procurement-stok.md
**Started**: 2026-07-12
**Updated**: 2026-07-12 14:45
**Status**: complete

---

## Tasks

- [x] 1. Database Migration: Add columns & statuses ✓
  - Files: `database/migrations/2026_07_12_000001_add_prd_flow_columns.php`, `database/seeders/StatusSeeder.php`
- [x] 2. Backend: Update PO Model & Controller ✓
  - Files: `app/Models/PurchaseOrder.php`, `app/Models/PurchaseOrderItemBahanBaku.php`, `app/Models/PurchaseOrderItemMesin.php`, `app/Http/Controllers/Api/Procurement/PurchaseOrderController.php`, `routes/api.php`
- [x] 3. Backend: Update Distribusi Controller ✓
  - Files: `app/Models/DistribusiBarang.php`, `app/Http/Controllers/api/Procurement/DistribusiController.php`
- [x] 4. Backend: Update Penerimaan Controller ✓
  - Files: `app/Http/Controllers/Api/Procurement/PenerimaanController.php`
- [x] 5. Backend: Update Retur Controller ✓
  - Files: `app/Http/Controllers/Api/Procurement/ReturController.php`
- [x] 6. Backend: Update Mutasi & Stock Controllers ✓
  - Files: `app/Http/Controllers/Api/Inventory/MutasiController.php`, `app/Http/Controllers/Api/Inventory/StockController.php`, `app/Models/MutasiStokPusatBahanBaku.php`, `app/Models/MutasiStokPusatMesin.php`
- [x] 7. Frontend: Update PO Pages ✓
  - Files: `frontend/src/pages/procurement/PurchaseOrderList.jsx`, `frontend/src/pages/procurement/PurchaseOrderDetail.jsx`, `frontend/src/api/purchaseOrders.js`
- [x] 8. Frontend: Update Distribusi Pages ✓
  - Files: `frontend/src/pages/procurement/DistributionForm.jsx`, `frontend/src/pages/procurement/DistributionDetail.jsx`, `frontend/src/api/distributions.js`
- [x] 9. Frontend: Update Retur & Mutasi Pages ✓
  - Files: `frontend/src/pages/procurement/ReturnList.jsx`, `frontend/src/pages/inventory/MutationForm.jsx`, `frontend/src/pages/inventory/MutationList.jsx`, `frontend/src/pages/inventory/StockList.jsx`, `frontend/src/utils/constants.js`
- [x] 10. Backend: Extract shared PO status check logic ✓
  - Note: checkPoSelesai() in ReturController, PO status updates in PenerimaanController

## Verify

| Check | Result |
|-------|--------|
| `php artisan migrate --pretend` | skipped (PHP 8.1 vs required 8.4) |
| `php artisan route:list --path=procurement` | skipped (PHP version) |
| `php artisan route:list --path=inventory` | skipped (PHP version) |

## Notes

- **Migration**: Added catatan_status (PO), qty_diterima (PO items), catatan (Distribusi), referensi_po_id (mutasi_stok_pusat)
- **Status additions**: diterima, diterima_sebagian for purchase_order; menunggu, ditolak for distribusi_barang
- **PO flow**: Status purely driven by Distribusi/Retur triggers, no manual status changes
- **Distribusi flow**: menunggu → ditolak/kirim/kirim_sebagian → diterima
- **Retur flow**: menunggu_pengganti → kirim_pengganti → selesai (auto PO selesai)
- **Mutasi**: penyesuaian removed, masuk requires PO reference (selesai only)
- **Stock**: Manual stok pusat input removed (POST /inventory/stocks route removed)
- **PHP version mismatch**: Cannot run artisan commands locally, syntax verified manually

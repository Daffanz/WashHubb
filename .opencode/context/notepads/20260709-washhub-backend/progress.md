# Progress: WashHub Backend API (Laravel 13)

**Plan**: .opencode/context/plans/20260709-washhub-backend.md
**Started**: 2026-07-09
**Updated**: 2026-07-09 01:00
**Status**: complete

---

## Tasks

### Wave 0: Foundation
- [x] 0.1 Scaffold Laravel 13 + Sanctum + Spatie + CORS ✓
- [x] 0.2 Statuses table + StatusSeeder ✓

### Wave 1: Account & Supplier
- [x] 1.1 Auth + RBAC (5 roles, permission matrix, admin user) ✓
- [x] 1.2 Supplier CRUD ✓

### Wave 2: Data Master
- [x] 2.1a KategoriBahanBaku + BahanBaku ✓
- [x] 2.1b JenisLayanan + pivot + Mesin ✓

### Wave 3: Procurement
- [x] 3.1a PurchaseOrder + PurchaseOrderItem ✓
- [x] 3.1b Distribusi + Penerimaan + StockService ✓

### Wave 4: Inventory
- [x] 4.1 StokPusat + MutasiStok ✓

### Refactor: Hapus Resources
- [x] Hapus semua Resource files ✓
- [x] Update semua controllers return array langsung ✓

## Verify

| Check | Result |
|-------|--------|
| `php artisan migrate:fresh --seed` | ✅ 20 tables, seeders OK |
| `php artisan route:list --path=api` | ✅ 59 routes |
| `php artisan test` | ✅ 2 tests passing |

## Notes

**Refactor**: Hapus `app/Http/Resources/` — semua controller return array langsung via `formatX()` private method.

**Architecture**:
- Controllers: `app/Http/Controllers/Api/` (Auth, User, Role, Permission, Supplier, Master/*, Procurement/*, Inventory/*)
- Form Requests: `app/Http/Requests/` (Auth, User, Supplier, Master, Procurement, Inventory)
- Services: `app/Services/StockService.php`
- Models: `app/Models/` (16 models)

**No Resources**: Response format langsung di controller. Lebih simple, kurang abstraction.

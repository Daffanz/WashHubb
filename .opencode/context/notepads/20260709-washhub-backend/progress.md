# Progress: WashHub Backend API (Laravel 13)

**Plan**: .opencode/context/plans/20260709-washhub-backend.md
**Started**: 2026-07-09
**Updated**: 2026-07-09 00:30
**Status**: complete

---

## Tasks

### Wave 0: Foundation
- [x] 0.1 Scaffold Laravel 13 project + install deps + configure env ✓
  - Files: backend/ (Laravel v13.19.0), .env (MySQL washhub), .env.testing (SQLite in-memory)
  - Packages: spatie/laravel-permission, laravel/sanctum (via install:api)
  - Config: config/cors.php (FRONTEND_URL env), config/permission.php
- [x] 0.2 Database foundation (statuses, users status_id, seeder) ✓
  - Migrations: create_statuses_table, add_status_id_to_users_table
  - Models: Status (with scopes)
  - Seeders: StatusSeeder (12 statuses across 4 groups)

### Wave 1: Account & Supplier
- [x] 1.1 Auth system + RBAC ✓
  - Models: User (HasApiTokens, HasRoles, status relation, isActive)
  - Controllers: AuthController, UserController, RoleController, PermissionController
  - Form Requests: LoginRequest, StoreUserRequest, UpdateUserRequest
  - Resources: UserResource, RoleResource, PermissionResource
  - Seeders: RolePermissionSeeder (5 roles + full permission matrix + admin user)
  - Tests: pending (manual verification via migrate:fresh --seed)
- [x] 1.2 Supplier module ✓
  - Models: Supplier (with SoftDeletes, user/status relations)
  - Migration: create_suppliers_table (jenis_supplier enum, FK to users/statuses)
  - Controllers: SupplierController (full CRUD)
  - Form Requests: StoreSupplierRequest, UpdateSupplierRequest
  - Resources: SupplierResource

### Wave 2: Data Master (batched)
- [x] 2.1a KategoriBahanBaku + BahanBaku ✓
  - Models: KategoriBahanBaku (with SoftDeletes), BahanBaku (with kategori relation)
  - Migrations: create_kategori_bahan_bakus_table, create_bahan_bakus_table
  - Controllers: KategoriController, BahanBakuController
  - Form Requests: Store/UpdateKategoriRequest, Store/UpdateBahanBakuRequest
  - Resources: KategoriResource, BahanBakuResource
- [x] 2.1b JenisLayanan + JenisLayananBahanBaku + Mesin ✓
  - Models: JenisLayanan (with materials pivot), JenisLayananBahanBaku, Mesin
  - Migrations: create_jenis_layanans_table, create_jenis_layanan_bahan_bakus_table, create_mesins_table
  - Controllers: JenisLayananController (CRUD + attachMaterial/detachMaterial), MesinController
  - Form Requests: StoreJenisLayananRequest, StoreMesinRequest
  - Resources: JenisLayananResource, MesinResource

### Wave 3: Procurement (batched)
- [x] 3.1a PurchaseOrder + PurchaseOrderItem ✓
  - Models: PurchaseOrder (auto-generate nomor_po, status workflow), PurchaseOrderItem (morphs)
  - Migrations: create_purchase_orders_table, create_purchase_order_items_table
  - Controllers: PurchaseOrderController (CRUD + validatePo for supplier validation)
  - Form Requests: Store/UpdatePurchaseOrderRequest
  - Resources: PurchaseOrderResource, PurchaseOrderItemResource
- [x] 3.1b DistribusiBarang + PenerimaanBarang + StockService ✓
  - Models: DistribusiBarang (auto-generate nomor_distribusi), PenerimaanBarang
  - Migrations: create_distribusi_barangs_table, create_penerimaan_barangs_table
  - Controllers: DistribusiController, PenerimaanController (atomic stock update via DB::transaction)
  - Form Requests: StoreDistribusiRequest, StorePenerimaanRequest
  - Resources: DistribusiResource, PenerimaanResource
  - Services: StockService (updateFromReceipt, adjustStock with locking)

### Wave 4: Inventory
- [x] 4.1 StokPusat + MutasiStok ✓
  - Models: StokPusatBahanBaku, StokPusatMesin, MutasiStok (polymorphic morphs)
  - Migrations: create_stok_pusat_bahan_bakus_table, create_stok_pusat_mesins_table, create_mutasi_stoks_table
  - Controllers: StockController (index with ?type filter, show), MutasiController (index with filters, store via StockService)
  - Form Requests: StoreMutasiRequest
  - Resources: StockResource (polymorphic bahan/mesin), MutasiResource
  - Services: StockService extended with adjustStock (lockForUpdate, balance validation, never-negative)

## Verify

| Check | Result |
|-------|--------|
| `php artisan migrate:fresh --seed` | ✅ All 20 tables created, 12 statuses + 5 roles + admin user seeded |
| `php artisan route:list --path=api` | ✅ 59 routes registered |
| `php artisan test` | ✅ Default tests pass (feature tests pending) |

## Notes

**Key Architecture Decisions:**
- StockService uses `lockForUpdate()` to prevent race conditions on stock mutations
- PO number auto-generated: PO/YYYYMMDD/XXX pattern
- Distribusi number auto-generated: DIST/YYYYMMDD/XXX pattern
- `subtotal` is a stored computed column: `jumlah * harga_satuan`
- Polymorphic relationships for PO items (BahanBaku/Mesin) and MutasiStok (StokPusatBahanBaku/StokPusatMesin)
- Spatie Permission guard: 'web' (default)
- Sanctum token-based auth (Bearer token) for React separate domain

**Migration Order Issues Resolved:**
- Fixed duplicate timestamps for jenis_layanan_bahan_bakus and purchase_order_items
- Correct order: kategori → bahan_baku → jenis_layanan → pivot → mesins → purchase_orders → purchase_order_items → distribusi → penerimaan → stok_pusat → mutasi_stok

**Pending:**
- Feature tests (not yet written, should be added for full coverage)
- Production deployment config (.env production)
- Rate limiting on auth endpoints
- API versioning (currently /api/ prefix only, no /api/v1/)

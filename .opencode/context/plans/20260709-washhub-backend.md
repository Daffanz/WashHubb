# Plan: WashHub Backend API (Laravel 13)

**ID**: 20260709-001
**Status**: ready
**Created**: 2026-07-09
**Author**: @architect

---

## TL;DR

Build REST API backend for franchise laundry management (WashHub) using Laravel 13, MySQL, Sanctum token auth, Spatie RBAC. 4 modules delivered sequentially: Account & Supplier → Data Master → Procurement → Inventory. Full test coverage.

---

## Context

### Background
Laundry franchise needs centralized backend to manage: user roles, suppliers, product catalog, purchase orders, inventory stock across outlets.

### Current State
Greenfield — empty `backend/` directory. No code exists.

### Target State
Laravel 13 API serving React frontend (separate domain). 5 RBAC roles. 13+ endpoint groups. 4 business modules with full CRUD + workflow transitions. Test coverage >70%.

---

## User Decisions

| Decision | Choice |
|----------|--------|
| Environment | PHP 8.4+, Laragon, MySQL |
| API Style | REST konvensional (Eloquent API Resources) |
| Auth | Sanctum token-based (Bearer) |
| Frontend | React separate domain/port |
| Delivery | Per modul (4 waves) |
| Testing | Full feature + unit tests |
| Naming | snake_case DB, camelCase PHP |

---

## Objectives

### Primary Goals
1. Fully functional REST API for all 4 modules
2. RBAC via Spatie Permission (5 roles)
3. Sanctum token auth for React frontend
4. Tested — every endpoint has auth + CRUD + validation tests

### Success Criteria
- [ ] All endpoints return correct HTTP status codes (200/201/400/401/403/404)
- [ ] Sanctum token auth works — login returns token, protected routes reject without token
- [ ] Spatie gates enforced — franchisor cannot access /api/users, admin_it cannot modify master data
- [ ] PO lifecycle works: create → send → validate → distribute → receive → stock update
- [ ] Stock mutations logged and balance consistent
- [ ] `php artisan test` passes with >70% coverage

### Non-Goals (Explicit Exclusions)
- No frontend/UI (React built separately)
- No reporting/dashboard endpoints
- No webhook integrations
- No real-time notifications
- No AI/ML features (despite Laravel 13 AI SDK)
- No file upload system

---

## Gap Analysis (Pre-Plan)

| Gap | Type | Resolution |
|-----|------|------------|
| No statuses table defined but `status_id` used in 4+ tables | CRITICAL | Create `statuses` migration + seeder. Seed with workflow states per module. |
| PO workflow state machine not defined | CRITICAL | Define explicit status transitions. Add `status` enum column to `purchase_orders`. |
| `mutasi_stok.stok_id` ambiguous (bahan vs mesin) | MINOR | Use polymorphic morphs (`stok_type` + `stok_id`) on `mutasi_stok`. |
| `harga` field in PO items — unit or total? | MINOR | Use `harga_satuan` + `subtotal` for clarity. |
| Supplier validation action unclear | AMBIGUOUS | Add `status_validasi` enum to `purchase_orders`: pending, disetujui, ditolak. |
| No audit trail for procurement/inventory | MINOR | Defer. Laravel default timestamps sufficient for now. |
| Stock update not atomic | MINOR | Wrap penerimaan + stock update in `DB::transaction`. |
| CORS not yet configured | CRITICAL | Configure `config/cors.php` for React origin. |

---

## Execution Waves

### Wave 0: Project Foundation (Sequential)

```
Task 0.1: Scaffold & Configure Laravel 13
    |
Task 0.2: Database Foundation (Statuses, User mods, Spatie migrations)
```

| Task ID | Description | Agent | Dependencies | Est. Time |
|---------|-------------|-------|--------------|-----------|
| 0.1 | Scaffold Laravel project, install deps, configure env | @build | none | 45m |
| 0.2 | Create statuses table, update users table, publish Spatie migration | @build | 0.1 | 30m |

**File Conflict Check**: 0.1 creates project files + config/*.php. 0.2 creates DB migrations only. No overlap.

---

### Wave 1: Module 1 — Account & Supplier (Sequential)

```
Task 1.1: Auth System + RBAC
    |
Task 1.2: Supplier Module
```

| Task ID | Description | Agent | Dependencies | Est. Time |
|---------|-------------|-------|--------------|-----------|
| 1.1 | User auth (Sanctum tokens), Spatie RBAC (roles/permissions seeders, controllers) | @build | 0.2 | 2h |
| 1.2 | Supplier CRUD (model, migration, controller, tests) | @build | 1.1 | 1.5h |

**File Conflict Check**: 1.1 creates User model mods + Auth/Role/Permission controllers + api.php routes. 1.2 creates Supplier model + controller + routes. 1.2 adds new routes to api.php (different section), no overlap.

---

### Wave 2: Module 2 — Data Master (Batched)

```
Task 2.1a: Category + Material models & controllers (batched)
Task 2.1b: Service + Machine models & controllers (batched)
       (both in one @build session)
```

| Task ID | Description | Agent | Dispatch | Dependencies | Est. Time |
|---------|-------------|-------|----------|--------------|-----------|
| 2.1a | KategoriBahanBaku + BahanBaku (models, migrations, controllers, tests) | @build | batched:data-master | 1.2 | 1.5h |
| 2.1b | JenisLayanan + JenisLayananBahanBaku + Mesin (models, migrations, controllers, tests) | @build | batched:data-master | 2.1a | 1.5h |

**File Conflict Check**: Both tasks in same batch. 2.1a creates kategori & bahan_baku files. 2.1b creates jenis_layanan & mesin files. Both add routes to api.php — but since they're batched (same subagent session), the subagent handles sequential writes. Safe.

---

### Wave 3: Module 3 — Procurement (Batched)

```
Task 3.1a: Purchase Order models + controller (batched)
Task 3.1b: Distribution + Receiving models + controllers (batched)
       (both in one @build session)
```

| Task ID | Description | Agent | Dispatch | Dependencies | Est. Time |
|---------|-------------|-------|----------|--------------|-----------|
| 3.1a | PurchaseOrder + PurchaseOrderItem (models, migrations, controllers, status workflow, tests) | @build | batched:procurement | 2.1b | 2h |
| 3.1b | DistribusiBarang + PenerimaanBarang (models, migrations, controllers, stock update logic, tests) | @build | batched:procurement | 3.1a | 1.5h |

**File Conflict Check**: 3.1a creates PO files. 3.1b creates distribusi + penerimaan files. Both add routes to api.php — batched handles safely.

---

### Wave 4: Module 4 — Inventory (Single Task)

```
Task 4.1: Inventory Module (single batch)
```

| Task ID | Description | Agent | Dependencies | Est. Time |
|---------|-------------|-------|--------------|-----------|
| 4.1 | StokPusatBahanBaku + StokPusatMesin + MutasiStok (models, migrations, controllers, stock mutation logic, tests) | @build | 3.1b | 2h |

**File Conflict Check**: Single task. No conflicts.

---

## Detailed Task Breakdown

### TODO 0.1: Scaffold & Configure Laravel 13

**Agent**: @build
**Estimated Time**: 45m
**Dependencies**: none

#### Objective
Create Laravel 13 project in `backend/` directory. Configure environment, install dependencies, set up Sanctum + Spatie.

#### Acceptance Criteria
- [ ] `composer create-project laravel/laravel backend --prefer-dist` succeeds
- [ ] `.env` configured: DB_DATABASE=washhub, DB_CONNECTION=mysql
- [ ] `composer require spatie/laravel-permission` installed
- [ ] `php artisan vendor:publish` for Spatie Permission (config + migration)
- [ ] Sanctum config published and configured for token-based API
- [ ] CORS config allows React origin (http://localhost:3000 or production URL)
- [ ] `php artisan migrate` runs without errors
- [ ] `php artisan test` passes (default Laravel tests)

#### Implementation Notes
- Use `^13.0` version constraint
- Sanctum is already in Laravel 13 core, just publish config
- Set `STATE_DOMAIN` in sanctum.php to null (token-based, not SPA)
- CORS: set `supports_credentials` to false (token-based doesn't need it)
- `allowed_origins` in cors.php: `[env('FRONTEND_URL', 'http://localhost:5173')]`
- Spatie: run `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
- Run `php artisan migrate` after publishing Spatie

#### QA Scenarios

| Scenario | Input | Expected Output | Verification |
|----------|-------|-----------------|--------------|
| Fresh migration | `php artisan migrate` | All tables created (users, personal_access_tokens, permissions, roles, model_has_roles, role_has_permissions) | `php artisan db:show` |
| CORS headers | OPTIONS request from React origin | 200 with CORS headers | curl with Origin header |
| App boots | `php artisan serve` | Laravel welcome page or JSON response at / | Browser or curl |

#### Files to Modify
- `.env` — DB credentials, APP_URL, FRONTEND_URL
- `config/cors.php` — allowed_origins
- `config/sanctum.php` — stateful mode disabled
- `config/app.php` — Spatie service provider (auto-discovered in Laravel 13, no action needed)

---

### TODO 0.2: Database Foundation

**Agent**: @build
**Estimated Time**: 30m
**Dependencies**: 0.1

#### Objective
Create statuses lookup table. Update users migration. Ensure Spatie migration published.

#### Acceptance Criteria
- [ ] `create_statuses_table` migration exists with: id, name, group (module), description
- [ ] `add_status_id_to_users_table` migration adds `status_id` FK to users
- [ ] StatusSeeder creates: active/inactive for users, aktif/non-aktif for supplier, draft/dikirim/disetujui/ditolak/diterima for PO
- [ ] `php artisan db:seed --class=StatusSeeder` works
- [ ] All foreign keys properly defined with onDelete cascade/restrict

#### Implementation Notes
- Statuses table: `id`, `name` (string), `group` (string — module context), `description` (text nullable)
- Group values: 'user', 'supplier', 'purchase_order', 'distribusi'
- Seed statuses via dedicated StatusSeeder (not DatabaseSeeder for now)
- Use `foreignId('status_id')->constrained()->cascadeOnDelete()` pattern
- Run `php artisan make:migration create_statuses_table`
- Run `php artisan make:migration add_status_id_to_users_table --table=users`

#### QA Scenarios

| Scenario | Input | Expected Output | Verification |
|----------|-------|-----------------|--------------|
| Seed statuses | `php artisan db:seed --class=StatusSeeder` | Statuses table populated | `php artisan tinker --execute="\App\Models\Status::count()"` |
| FK constraint | Insert user with invalid status_id | SQL error | Test via tinker |

#### Files to Create
- `database/migrations/xxxx_xx_xx_create_statuses_table.php`
- `database/migrations/xxxx_xx_xx_add_status_id_to_users_table.php`
- `database/seeders/StatusSeeder.php`
- `app/Models/Status.php`

---

### TODO 1.1: Auth System + RBAC

**Agent**: @build
**Dispatch**: single
**Estimated Time**: 2h
**Dependencies**: 0.2

#### Objective
Build authentication system with Sanctum token-based auth. Implement Spatie RBAC with 5 roles and full permission matrix.

#### Acceptance Criteria
- [ ] POST /api/auth/login returns token + user data (200)
- [ ] POST /api/auth/logout revokes token (200)
- [ ] GET /api/auth/me returns authenticated user (200)
- [ ] CRUD /api/users — only admin_it can access
- [ ] CRUD /api/roles — only admin_it can access
- [ ] CRUD /api/permissions — only admin_it can access
- [ ] 5 roles seeded: admin_it, franchisor, procurement, supplier, manager_outlet
- [ ] Permission matrix enforced via Spatie gates
- [ ] All endpoints return 401 without token, 403 for wrong role
- [ ] Feature tests for auth flow + permission gates

#### Implementation Notes
- User model: add `HasApiTokens` (Sanctum) + `HasRoles` (Spatie) traits
- AuthController: `login()` validates email+password, creates token via `$user->createToken()`, returns plainTextToken
- AuthController: `logout()` revokes current token
- AuthController: `me()` returns user with roles/permissions loaded
- Use `#[Middleware('auth:sanctum')]` attribute on protected controllers
- Use `#[Middleware('permission:user-list|user-create|...')]` or Spatie's `->middleware('permission:...')` in routes
- Form Requests: `StoreUserRequest`, `UpdateUserRequest`, `LoginRequest`
- API Resources: `UserResource`, `RoleResource`, `PermissionResource`
- Routes in `routes/api.php` under appropriate groups

#### Permission Matrix (Seed)

```
admin_it:
  user-list, user-create, user-edit, user-delete
  role-list, role-create, role-edit, role-delete
  permission-list

franchisor:
  category-list, category-create, category-edit, category-delete
  material-list, material-create, material-edit, material-delete
  service-list, service-create, service-edit, service-delete
  machine-list, machine-create, machine-edit, machine-delete

procurement:
  supplier-list, supplier-create, supplier-edit
  po-list, po-create, po-edit
  distribution-list, distribution-create
  receipt-list, receipt-create

supplier:
  po-list, po-validate

manager_outlet:
  stock-list, stock-view
  mutation-list, mutation-create
```

#### QA Scenarios

| Scenario | Input | Expected Output | Verification |
|----------|-------|-----------------|--------------|
| Login success | POST /api/auth/login {email, password} | 200 + {token, user} | Assert token string returned |
| Login wrong password | Same, wrong password | 401 + error | Assert 401 |
| Protected route | GET /api/users without token | 401 | Assert 401 |
| Forbidden | GET /api/users as franchisor | 403 | Assert 403 |
| Role CRUD | admin_it creates role | 201 + role data | Assert role created |
| Permission check | franchisor tries to create user | 403 | Assert 403 |

#### Files to Modify
- `app/Models/User.php` — add HasApiTokens, HasRoles traits
- `routes/api.php` — add auth + user + role + permission routes

#### Files to Create
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/UserController.php`
- `app/Http/Controllers/Api/RoleController.php`
- `app/Http/Controllers/Api/PermissionController.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Requests/User/StoreUserRequest.php`
- `app/Http/Requests/User/UpdateUserRequest.php`
- `app/Http/Resources/UserResource.php`
- `app/Http/Resources/RoleResource.php`
- `app/Http/Resources/PermissionResource.php`
- `database/seeders/RolePermissionSeeder.php`
- `tests/Feature/AuthTest.php`
- `tests/Feature/UserTest.php`
- `tests/Feature/RolePermissionTest.php`

---

### TODO 1.2: Supplier Module

**Agent**: @build
**Dispatch**: single
**Estimated Time**: 1.5h
**Dependencies**: 1.1

#### Objective
Build Supplier CRUD with types (bahan_baku/mesin), linked to users.

#### Acceptance Criteria
- [ ] CRUD /api/suppliers — procurement + admin_it can access
- [ ] Supplier model: user_id, jenis_supplier (enum: bahan_baku, mesin), alamat, status_id
- [ ] Relation: Supplier belongsTo User, User hasMany Supplier
- [ ] Validation: jenis_supplier must be valid enum value
- [ ] Feature tests for CRUD + permission gates

#### Implementation Notes
- Model: `Supplier` with `fillable`, `casts` (jenis_supplier as string), `belongsTo(User)`
- Migration: `create_suppliers_table` with foreign keys (user_id, status_id)
- Controller: `SupplierController` with standard CRUD
- Use `php artisan make:controller Api/SupplierController --resource --model=Supplier`
- API Resource: `SupplierResource`
- Routes under `api.php` with `permission:supplier-list|supplier-create|supplier-edit` middleware
- Procurement role can create/edit suppliers. Admin_it can do all.

#### QA Scenarios

| Scenario | Input | Expected Output | Verification |
|----------|-------|-----------------|--------------|
| List suppliers | GET /api/suppliers | Paginated supplier list | Assert 200 + data array |
| Create supplier | POST /api/suppliers with valid data | 201 + supplier | Assert DB has record |
| Invalid jenis | POST with jenis_supplier = 'invalid' | 422 + validation error | Assert error message |
| Forbidden | manager_outlet tries POST | 403 | Assert 403 |

#### Files to Modify
- `routes/api.php` — add supplier routes

#### Files to Create
- `app/Models/Supplier.php`
- `database/migrations/xxxx_xx_xx_create_suppliers_table.php`
- `app/Http/Controllers/Api/SupplierController.php`
- `app/Http/Requests/Supplier/StoreSupplierRequest.php`
- `app/Http/Requests/Supplier/UpdateSupplierRequest.php`
- `app/Http/Resources/SupplierResource.php`
- `tests/Feature/SupplierTest.php`

---

### TODO 2.1a: Category & Material Models

**Agent**: @build
**Dispatch**: batched:data-master
**Estimated Time**: 1.5h
**Dependencies**: 1.2

#### Objective
Build KategoriBahanBaku and BahanBaku CRUD for master data.

#### Acceptance Criteria
- [ ] CRUD /api/master/categories — franchisor + admin_it can access
- [ ] CRUD /api/master/materials — franchisor + admin_it can access
- [ ] KategoriBahanBaku: id, nama
- [ ] BahanBaku: id, kategori_id FK, nama, satuan, harga_standar
- [ ] Relation: Kategori hasMany BahanBaku, BahanBaku belongsTo Kategori
- [ ] Feature tests for CRUD + permission gates

#### Implementation Notes
- Use `SoftDeletes` on both models for safe deletion
- Foreign key `kategori_id` with cascadeOnDelete
- API Resources for response transformation
- Form Requests for validation
- Franchisor-only via Spatie middleware

#### QA Scenarios

| Scenario | Input | Expected Output | Verification |
|----------|-------|-----------------|--------------|
| Create category | POST with nama | 201 | Record in DB |
| Create material | POST with kategori_id, nama, satuan, harga_standar | 201 | Check relation |
| Soft delete | DELETE /api/master/categories/{id} | 200 + deleted_at set | DB check |
| Forbidden | procurement tries POST | 403 | Assert |

#### Files to Modify
- `routes/api.php` — add master routes

#### Files to Create
- `app/Models/KategoriBahanBaku.php`
- `app/Models/BahanBaku.php`
- `database/migrations/xxxx_xx_xx_create_kategori_bahan_bakus_table.php`
- `database/migrations/xxxx_xx_xx_create_bahan_bakus_table.php`
- `app/Http/Controllers/Api/Master/KategoriController.php`
- `app/Http/Controllers/Api/Master/BahanBakuController.php`
- `app/Http/Requests/Master/StoreKategoriRequest.php`
- `app/Http/Requests/Master/UpdateKategoriRequest.php`
- `app/Http/Requests/Master/StoreBahanBakuRequest.php`
- `app/Http/Requests/Master/UpdateBahanBakuRequest.php`
- `app/Http/Resources/Master/KategoriResource.php`
- `app/Http/Resources/Master/BahanBakuResource.php`
- `tests/Feature/Master/KategoriTest.php`
- `tests/Feature/Master/BahanBakuTest.php`

---

### TODO 2.1b: Service & Machine Models

**Agent**: @build
**Dispatch**: batched:data-master
**Estimated Time**: 1.5h
**Dependencies**: 2.1a

#### Objective
Build JenisLayanan, JenisLayananBahanBaku (pivot mapping), and Mesin CRUD.

#### Acceptance Criteria
- [ ] CRUD /api/master/services — franchisor + admin_it can access
- [ ] CRUD /api/master/machines — franchisor + admin_it can access
- [ ] JenisLayanan: id, nama, harga_standar_per_kg
- [ ] JenisLayananBahanBaku: id, jenis_layanan_id FK, bahan_baku_id FK, jumlah_konsumsi
- [ ] Mesin: id, nama, kode_mesin, merk, tipe, kapasitas
- [ ] JenisLayanan hasMany JenisLayananBahanBaku, BahanBaku hasMany through pivot
- [ ] Feature tests for CRUD + service-material mapping

#### Implementation Notes
- JenisLayananBahanBaku is a junction/pivot with extra field (jumlah_konsumsi)
- Use `belongsToMany` with `withPivot('jumlah_konsumsi')` or define explicit JenisLayananBahanBaku model
- Since pivot has extra data, explicit model recommended
- Service-material mapping: POST to /api/master/services/{id}/materials to attach
- SoftDeletes on all master data models

#### QA Scenarios

| Scenario | Input | Expected Output | Verification |
|----------|-------|-----------------|--------------|
| Create service | POST with nama, harga_standar_per_kg | 201 | DB record |
| Create machine | POST with nama, kode_mesin, merk, tipe, kapasitas | 201 | DB record |
| Map material to service | POST /api/master/services/{id}/materials {bahan_baku_id, jumlah_konsumsi} | 200 | Pivot table has record |
| List service with materials | GET /api/master/services/{id}?with=materials | 200 + materials array | Assert relation loaded |

#### Files to Modify
- `routes/api.php` — add service + machine + mapping routes

#### Files to Create
- `app/Models/JenisLayanan.php`
- `app/Models/JenisLayananBahanBaku.php`
- `app/Models/Mesin.php`
- `database/migrations/xxxx_xx_xx_create_jenis_layanans_table.php`
- `database/migrations/xxxx_xx_xx_create_jenis_layanan_bahan_bakus_table.php`
- `database/migrations/xxxx_xx_xx_create_mesins_table.php`
- `app/Http/Controllers/Api/Master/JenisLayananController.php`
- `app/Http/Controllers/Api/Master/MesinController.php`
- `app/Http/Requests/Master/StoreJenisLayananRequest.php`
- `app/Http/Requests/Master/StoreMesinRequest.php`
- `app/Http/Resources/Master/JenisLayananResource.php`
- `app/Http/Resources/Master/MesinResource.php`
- `tests/Feature/Master/JenisLayananTest.php`
- `tests/Feature/Master/MesinTest.php`

---

### TODO 3.1a: Purchase Order System

**Agent**: @build
**Dispatch**: batched:procurement
**Estimated Time**: 2h
**Dependencies**: 2.1b

#### Objective
Build PurchaseOrder and PurchaseOrderItem with full status workflow.

#### Acceptance Criteria
- [ ] CRUD /api/procurement/purchase-orders — procurement can create/edit, supplier can list/validate
- [ ] PO model: nomor_po, supplier_id FK, jenis_po (bahan_baku/mesin), status_validasi (pending/disetujui/ditolak), status_id FK
- [ ] PO Item model: po_id FK, item_id (polymorphic — bahan_baku_id or mesin_id), jumlah, harga_satuan, subtotal
- [ ] PO number autogenerated: PO/YYYYMMDD/XXX
- [ ] Status workflow: draft → dikirim (status_id) → supplier validasi (status_validasi) → disetujui/ditolak
- [ ] Supplier only sees their own POs
- [ ] Feature tests for PO lifecycle

#### Implementation Notes
- Use `DB::transaction` for PO creation with items
- `nomor_po` auto-generated in `creating` model boot event
- `jenis_po` determines whether items reference bahan_baku or mesin
- Use polymorphic relationship for PO items (`item_type` + `item_id`) or separate nullable FKs — polymorphic cleaner
- PO status transitions should be validated (don't allow draft→approved directly)
- Supplier validates via PATCH endpoint: `/api/procurement/purchase-orders/{id}/validate`
- Query scopes: `scopeForSupplier($userId)` for filtering

#### QA Scenarios

| Scenario | Input | Expected Output | Verification |
|----------|-------|-----------------|--------------|
| Create PO with items | POST full payload with items array | 201 + PO with items | Both PO + items in DB |
| Auto-generate PO number | Create PO | nomor_po like PO/20260709/001 | Assert format |
| Send PO to supplier | PATCH status_id to 'dikirim' | 200, status updated | Assert DB status |
| Supplier validates | PATCH /validate {status_validasi: disetujui} | 200 | Assert status_validasi |
| Wrong status transition | PATCH from draft to approved directly | 422 | Assert validation error |
| Supplier sees own POs | GET /api/procurement/purchase-orders as supplier | Only their POs | Assert filtered |

#### Files to Modify
- `routes/api.php` — add procurement routes

#### Files to Create
- `app/Models/PurchaseOrder.php`
- `app/Models/PurchaseOrderItem.php`
- `database/migrations/xxxx_xx_xx_create_purchase_orders_table.php`
- `database/migrations/xxxx_xx_xx_create_purchase_order_items_table.php`
- `app/Http/Controllers/Api/Procurement/PurchaseOrderController.php`
- `app/Http/Requests/Procurement/StorePurchaseOrderRequest.php`
- `app/Http/Requests/Procurement/UpdatePurchaseOrderRequest.php`
- `app/Http/Requests/Procurement/ValidatePurchaseOrderRequest.php`
- `app/Http/Resources/Procurement/PurchaseOrderResource.php`
- `tests/Feature/Procurement/PurchaseOrderTest.php`

---

### TODO 3.1b: Distribution & Receiving

**Agent**: @build
**Dispatch**: batched:procurement
**Estimated Time**: 1.5h
**Dependencies**: 3.1a

#### Objective
Build DistribusiBarang and PenerimaanBarang. Atomically update stock on receipt.

#### Acceptance Criteria
- [ ] CRUD /api/procurement/distributions — procurement can create
- [ ] CRUD /api/procurement/receipts — procurement can create
- [ ] Distribution model: po_id FK, nomor_distribusi, status_id FK
- [ ] Receipt model: distribusi_barang_id FK, tanggal_terima, total_bayar
- [ ] Creating receipt atomically updates StokPusatBahanBaku or StokPusatMesin
- [ ] Distribution number auto-generated: DIST/YYYYMMDD/XXX
- [ ] Stock update uses DB transaction
- [ ] Feature tests for distribution → receipt → stock verification

#### Implementation Notes
- `DistribusiBarang` hasMany `PenerimaanBarang` (partial deliveries possible)
- Use `DB::transaction` in `PenerimaanBarangController::store`
- On receipt creation, query PO items → determine item type → update appropriate stock:
  - If PO jenis_po = 'bahan_baku': update StokPusatBahanBaku
  - If PO jenis_po = 'mesin': update StokPusatMesin
- Create or update stock record (first receipt might need to create stock record)
- Stock update logic in a service class `StockService::updateFromReceipt($penerimaan)`

#### QA Scenarios

| Scenario | Input | Expected Output | Verification |
|----------|-------|-----------------|--------------|
| Create distribution | POST with po_id | 201 + distribution | DB record |
| Create receipt | POST with distribusi_barang_id, tanggal_terima, total_bayar | 201 | Stock updated |
| Stock increased | Check stock after receipt | stok_saat_ini > 0 | Assert stock value |
| Partial receipt | Create 2nd receipt for same distribution | Both receipts exist | Total stock sums correctly |

#### Files to Modify
- `routes/api.php` — add distribution + receipt routes

#### Files to Create
- `app/Models/DistribusiBarang.php`
- `app/Models/PenerimaanBarang.php`
- `database/migrations/xxxx_xx_xx_create_distribusi_barangs_table.php`
- `database/migrations/xxxx_xx_xx_create_penerimaan_barangs_table.php`
- `app/Http/Controllers/Api/Procurement/DistribusiController.php`
- `app/Http/Controllers/Api/Procurement/PenerimaanController.php`
- `app/Http/Requests/Procurement/StoreDistribusiRequest.php`
- `app/Http/Requests/Procurement/StorePenerimaanRequest.php`
- `app/Http/Resources/Procurement/DistribusiResource.php`
- `app/Http/Resources/Procurement/PenerimaanResource.php`
- `app/Services/StockService.php`
- `tests/Feature/Procurement/DistributionTest.php`
- `tests/Feature/Procurement/ReceiptTest.php`

---

### TODO 4.1: Inventory Module

**Agent**: @build
**Dispatch**: single
**Estimated Time**: 2h
**Dependencies**: 3.1b

#### Objective
Build central stock management (bahan baku + mesin) and stock mutation logging.

#### Acceptance Criteria
- [ ] GET /api/inventory/stocks — returns combined or separate stock for bahan & mesin
- [ ] StokPusatBahanBaku: bahan_baku_id FK, stok_saat_ini decimal
- [ ] StokPusatMesin: mesin_id FK, stok_saat_ini int
- [ ] POST /api/inventory/mutations — create stock mutation entry
- [ ] GET /api/inventory/mutations — list mutations with filters (date range, jenis_mutasi)
- [ ] MutasiStok: polymorphic (stok_type + stok_id), jenis_mutasi (in/out/adjustment), jumlah, tanggal, keterangan
- [ ] Mutation automatically adjusts stock balance atomically
- [ ] manager_outlet + procurement can access inventory
- [ ] Feature tests: stock view, mutation create, balance consistency

#### Implementation Notes
- Use polymorphic relationship: `morphTo('stok')` on MutasiStok
- Stock mutation happens in `DB::transaction` inside MutasiController::store
- Mutation flow: validate stock for out/adjustment → create mutation → update stock balance
- Stock balance never goes negative (validate before out mutations)
- GET /api/inventory/stocks can accept `?type=bahan` or `?type=mesin` filter
- Return stock with related model (bahan_baku or mesin) included

#### Stock Service Extension
- Extend `StockService.php` (from Task 3.1b) with:
  - `adjustStock($stokType, $stokId, $jumlah, $jenisMutasi, $keterangan)`
  - `getStockWithHistory($stokType, $stokId)`
  - All operations wrapped in DB transaction

#### QA Scenarios

| Scenario | Input | Expected Output | Verification |
|----------|-------|-----------------|--------------|
| View stock | GET /api/inventory/stocks | Paginated stock list | Assert data array |
| Create mutation | POST /api/inventory/mutations with valid data | 201 | Stock balance updated |
| Negative stock prevented | POST out mutation more than current stock | 422 | Assert validation error |
| Mutation history | GET /api/inventory/mutations?stok_type=App\Models\StokPusatBahanBaku&stok_id=1 | Mutation list for that stock | Assert filtered |
| Polymorphic morph | Check MutasiStok stok relationship | Related stock model | Assert morph loads correctly |

#### Files to Modify
- `routes/api.php` — add inventory routes
- `app/Services/StockService.php` — extend with adjustStock method

#### Files to Create
- `app/Models/StokPusatBahanBaku.php`
- `app/Models/StokPusatMesin.php`
- `app/Models/MutasiStok.php`
- `database/migrations/xxxx_xx_xx_create_stok_pusat_bahan_bakus_table.php`
- `database/migrations/xxxx_xx_xx_create_stok_pusat_mesins_table.php`
- `database/migrations/xxxx_xx_xx_create_mutasi_stoks_table.php`
- `app/Http/Controllers/Api/Inventory/StockController.php`
- `app/Http/Controllers/Api/Inventory/MutasiController.php`
- `app/Http/Requests/Inventory/StoreMutasiRequest.php`
- `app/Http/Resources/Inventory/StockResource.php`
- `app/Http/Resources/Inventory/MutasiResource.php`
- `tests/Feature/Inventory/StockTest.php`
- `tests/Feature/Inventory/MutasiTest.php`

---

## Dependency Matrix

```
Wave 0: [0.1] → [0.2]
             |
Wave 1: [1.1] → [1.2]
                  |
Wave 2: [2.1a] → [2.1b]  (batched)
                  |
Wave 3: [3.1a] → [3.1b]  (batched)
                  |
Wave 4: [4.1]
```

---

## Verification Protocol

### Per-Wave Checks
- [ ] `php artisan migrate:fresh --seed` runs cleanly
- [ ] `php artisan test` passes for that module's tests
- [ ] All new endpoints return correct HTTP codes via curl/Postman check
- [ ] Permission gates verified (test with each role)

### Final Integration
- [ ] Full `php artisan test` passes (all modules)
- [ ] API response format consistent across all endpoints
- [ ] No route conflicts or middleware gaps
- [ ] CORS preflight works from React origin

### Automated Checks
- [ ] `php artisan test --coverage` reports >70% coverage
- [ ] `php artisan route:list` shows all expected routes with middleware
- [ ] `php artisan migrate:fresh --seed` idempotent

---

## Documentation Updates

| Document | Update Required | Owner |
|----------|-----------------|-------|
| `docs/api/endpoints.md` | Document all endpoints after build | @curator (post-build) |
| `docs/api/auth.md` | Auth flow documentation | @curator (post-build) |
| `README.md` | API overview, setup instructions | @curator (post-build) |

---

## Risks and Mitigations

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Spatie + Sanctum integration issues | Low | High | Test auth+gates combo in Wave 1.1 early |
| PO workflow state machine complexity | Medium | Medium | Define explicit state transitions in plan. Validate in FormRequest. |
| Race condition on stock update | Low | High | Wrap all stock operations in `DB::transaction` with `sharedLock` |
| CORS misconfiguration blocks frontend | Low | Medium | Test with curl Origin header in Wave 0 |
| Migration conflicts between modules | Low | Medium | Sequential waves ensure no conflict. Run fresh migrate per wave. |
| Time estimation underestimation | Medium | Low | Each task time includes test writing. Add buffer if needed. |

---

## Assumptions

1. **PHP 8.4+ tersedia di Laragon**: Needed for Laravel 13 (min PHP 8.3)
2. **MySQL ready via Laragon**: Database will be created via `php artisan db:create` or manually
3. **React frontend dibangun terpisah**: No Inertia/Livewire — pure API
4. **Status workflow cukup dengan current design**: No complex state machine library needed
5. **Spatie Permission auto-discovery**: Laravel 13 auto-discovers service providers
6. **Stock mutation cukup via API langsung**: No queue needed for current volume
7. **PO items reference existing master data**: BahanBaku or Mesin must exist before PO creation
8. **Hanya 1 outlet concept untuk MVP**: Multi-outlet inventory bisa ditambahkan nanti

---

## Open Questions

- [ ] Production database name — currently assumed `washhub`
- [ ] API versioning prefix — currently just `/api/`, no `/api/v1/`
- [ ] Pagination size default — assumed 15 per page
- [ ] Soft deletes on all master data? — assumed yes for Kategori, BahanBaku, JenisLayanan, Mesin
- [ ] Error response format — assumed `{message: string, errors: object}` for 422

---

## Appendix

### Related Documents
- `PRD_WashHub_Backend_Laravel13.md` — source PRD
- `.opencode/context/drafts/20260709-washhub-backend.md` — planning draft

### Database Migration Order
1. `create_statuses_table`
2. `add_status_id_to_users_table`
3. Spatie: `create_permission_tables`
4. `create_suppliers_table`
5. `create_kategori_bahan_bakus_table`
6. `create_bahan_bakus_table`
7. `create_jenis_layanans_table`
8. `create_jenis_layanan_bahan_bakus_table`
9. `create_mesins_table`
10. `create_purchase_orders_table`
11. `create_purchase_order_items_table`
12. `create_distribusi_barangs_table`
13. `create_penerimaan_barangs_table`
14. `create_stok_pusat_bahan_bakus_table`
15. `create_stok_pusat_mesins_table`
16. `create_mutasi_stoks_table`

### Seeder Order
1. `StatusSeeder` — status lookup values
2. `RolePermissionSeeder` — 5 roles + permission matrix
3. `DatabaseSeeder` — combine all seeders

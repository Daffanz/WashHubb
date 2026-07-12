<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\Master\KategoriController;
use App\Http\Controllers\Api\Master\BahanBakuController;
use App\Http\Controllers\Api\Master\JenisLayananController;
use App\Http\Controllers\Api\Master\MesinController;
use App\Http\Controllers\Api\Procurement\PurchaseOrderController;
use App\Http\Controllers\Api\Procurement\DistribusiController;
use App\Http\Controllers\Api\Procurement\PenerimaanController;
use App\Http\Controllers\Api\Procurement\ReturController;
use App\Http\Controllers\Api\SupplierStockController;
use App\Http\Controllers\Api\Inventory\StockController;
use App\Http\Controllers\Api\Inventory\MutasiController;
use App\Http\Controllers\Api\ForgotPasswordController;
use Illuminate\Support\Facades\Route;

// Auth (public)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password/check-email', [ForgotPasswordController::class, 'checkEmail']);
    Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Statuses
    Route::get('/statuses', [StatusController::class, 'index']);
    Route::get('/statuses/{konteks}', [StatusController::class, 'byContext']);

    // --- Modul 1: Account & Supplier ---
    Route::middleware('permission:user-list')->get('/users', [UserController::class, 'index']);
    Route::middleware('permission:user-create')->post('/users', [UserController::class, 'store']);
    Route::middleware('permission:user-list')->get('/users/{user}', [UserController::class, 'show']);
    Route::middleware('permission:user-edit')->put('/users/{user}', [UserController::class, 'update']);
    Route::middleware('permission:user-delete')->delete('/users/{user}', [UserController::class, 'destroy']);

    Route::middleware('permission:role-list')->get('/roles', [RoleController::class, 'index']);
    Route::middleware('permission:role-create')->post('/roles', [RoleController::class, 'store']);
    Route::middleware('permission:role-list')->get('/roles/{role}', [RoleController::class, 'show']);
    Route::middleware('permission:role-edit')->put('/roles/{role}', [RoleController::class, 'update']);
    Route::middleware('permission:role-delete')->delete('/roles/{role}', [RoleController::class, 'destroy']);

    Route::middleware('permission:permission-list')->get('/permissions', [PermissionController::class, 'index']);
    Route::middleware('permission:permission-list')->get('/permissions/{permission}', [PermissionController::class, 'show']);

    Route::middleware('permission:supplier-list')->get('/suppliers', [SupplierController::class, 'index']);
    Route::middleware('permission:supplier-create')->post('/suppliers', [SupplierController::class, 'store']);
    Route::middleware('permission:supplier-list')->get('/suppliers/{supplier}', [SupplierController::class, 'show']);
    Route::middleware('permission:supplier-list')->get('/suppliers/{supplier}/items', [SupplierController::class, 'items']);
    Route::middleware('permission:supplier-edit')->put('/suppliers/{supplier}', [SupplierController::class, 'update']);
    Route::middleware('permission:supplier-delete')->delete('/suppliers/{supplier}', [SupplierController::class, 'destroy']);

    // --- Modul 2: Data Master ---
    Route::prefix('master')->group(function () {
        Route::middleware('permission:category-list')->get('/categories', [KategoriController::class, 'index']);
        Route::middleware('permission:category-create')->post('/categories', [KategoriController::class, 'store']);
        Route::middleware('permission:category-list')->get('/categories/{kategori}', [KategoriController::class, 'show']);
        Route::middleware('permission:category-edit')->put('/categories/{kategori}', [KategoriController::class, 'update']);
        Route::middleware('permission:category-delete')->delete('/categories/{kategori}', [KategoriController::class, 'destroy']);

        Route::middleware('permission:material-list')->get('/materials', [BahanBakuController::class, 'index']);
        Route::middleware('permission:material-create')->post('/materials', [BahanBakuController::class, 'store']);
        Route::middleware('permission:material-list')->get('/materials/{bahanBaku}', [BahanBakuController::class, 'show']);
        Route::middleware('permission:material-edit')->put('/materials/{bahanBaku}', [BahanBakuController::class, 'update']);
        Route::middleware('permission:material-delete')->delete('/materials/{bahanBaku}', [BahanBakuController::class, 'destroy']);

        Route::middleware('permission:service-list')->get('/services', [JenisLayananController::class, 'index']);
        Route::middleware('permission:service-create')->post('/services', [JenisLayananController::class, 'store']);
        Route::middleware('permission:service-list')->get('/services/{jenisLayanan}', [JenisLayananController::class, 'show']);
        Route::middleware('permission:service-edit')->put('/services/{jenisLayanan}', [JenisLayananController::class, 'update']);
        Route::middleware('permission:service-delete')->delete('/services/{jenisLayanan}', [JenisLayananController::class, 'destroy']);
        Route::middleware('permission:service-create')->post('/services/{jenisLayanan}/materials', [JenisLayananController::class, 'attachMaterial']);
        Route::middleware('permission:service-edit')->delete('/services/{jenisLayanan}/materials/{bahanBaku}', [JenisLayananController::class, 'detachMaterial']);

        Route::middleware('permission:machine-list')->get('/machines', [MesinController::class, 'index']);
        Route::middleware('permission:machine-create')->post('/machines', [MesinController::class, 'store']);
        Route::middleware('permission:machine-list')->get('/machines/{mesin}', [MesinController::class, 'show']);
        Route::middleware('permission:machine-edit')->put('/machines/{mesin}', [MesinController::class, 'update']);
        Route::middleware('permission:machine-delete')->delete('/machines/{mesin}', [MesinController::class, 'destroy']);
    });

    // --- Modul 3: Procurement ---
    Route::prefix('procurement')->group(function () {
        Route::middleware('permission:po-list')->get('/purchase-orders', [PurchaseOrderController::class, 'index']);
        Route::middleware('permission:po-create')->post('/purchase-orders', [PurchaseOrderController::class, 'store']);
        Route::middleware('permission:po-list')->get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
        Route::middleware('permission:po-edit')->put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update']);
        Route::middleware('permission:po-delete')->delete('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);
        // UC-30: Kirim PO ke Supplier
        Route::middleware('permission:po-kirim')->patch('/purchase-orders/{purchaseOrder}/kirim', [PurchaseOrderController::class, 'kirim']);
        // UC-31: Supplier validasi per-item
        Route::middleware('permission:po-validate')->patch('/purchase-orders/{purchaseOrder}/validate', [PurchaseOrderController::class, 'validate']);

        // Supplier Stock (UC-39/40)
        Route::middleware('permission:supplier-stock-manage')->get('/supplier-stocks', [SupplierStockController::class, 'index']);
        Route::middleware('permission:supplier-stock-manage')->get('/supplier-stocks/{id}', [SupplierStockController::class, 'show']);
        Route::middleware('permission:supplier-stock-manage')->post('/supplier-stocks', [SupplierStockController::class, 'store']);
        Route::middleware('permission:supplier-stock-manage')->delete('/supplier-stocks/{id}', [SupplierStockController::class, 'destroy']);

        // UC-35/36: Distribusi
        Route::middleware('permission:distribution-list')->get('/distributions', [DistribusiController::class, 'index']);
        Route::middleware('permission:distribution-create')->post('/distributions', [DistribusiController::class, 'store']);
        Route::middleware('permission:distribution-list')->get('/distributions/{distribusi}', [DistribusiController::class, 'show']);
        Route::middleware('permission:receipt-create')->patch('/distributions/{distribusi}/diterima', [DistribusiController::class, 'diterima']);

        // UC-34: Penerimaan
        Route::middleware('permission:receipt-list')->get('/receipts', [PenerimaanController::class, 'index']);
        Route::middleware('permission:receipt-create')->post('/receipts', [PenerimaanController::class, 'store']);
        Route::middleware('permission:receipt-list')->get('/receipts/{penerimaan}', [PenerimaanController::class, 'show']);

        // UC-37/38: Retur
        Route::middleware('permission:retur-list')->get('/returns', [ReturController::class, 'index']);
        Route::middleware('permission:retur-create')->post('/returns', [ReturController::class, 'store']);
        Route::middleware('permission:retur-list')->get('/returns/{retur}', [ReturController::class, 'show']);
        Route::middleware('permission:retur-list')->patch('/returns/{retur}/kirim-pengganti', [ReturController::class, 'kirimPengganti']);
        Route::middleware('permission:retur-validate')->patch('/returns/{retur}/confirm', [ReturController::class, 'confirm']);
    });

    // --- Modul 4: Inventory ---
    Route::prefix('inventory')->group(function () {
        Route::middleware('permission:stock-list')->get('/stocks', [StockController::class, 'index']);
        Route::middleware('permission:stock-view')->get('/stocks/{type}/{id}', [StockController::class, 'show']);

        Route::middleware('permission:mutation-list')->get('/mutations', [MutasiController::class, 'index']);
        Route::middleware('permission:mutation-create')->post('/mutations', [MutasiController::class, 'store']);
    });
});

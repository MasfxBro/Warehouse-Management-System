<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\MasterBarangApiController;
use App\Http\Controllers\Api\InboundApiController;
use App\Http\Controllers\Api\OutboundApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\MasterDataApiController;
use App\Http\Controllers\Api\StockOpnameApiController;

/*
|--------------------------------------------------------------------------
| WMS Flutter API Routes
|--------------------------------------------------------------------------
|
| Semua route di sini berada di bawah prefix /api/
| Autentikasi menggunakan Laravel Sanctum (Bearer Token).
|
| Flow: Flutter → POST /api/login → dapat token → kirim di setiap request
|       sebagai header: Authorization: Bearer {token}
|
*/

// ============================================================
// PUBLIC ROUTES (Tidak perlu token)
// ============================================================
Route::post('/login', [AuthApiController::class, 'login']);

// ============================================================
// PROTECTED ROUTES (Butuh token Sanctum)
// ============================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- Autentikasi ---
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);

    // --- Dashboard ---
    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    // --- Master Data Barang ---
    Route::get('/barang', [MasterBarangApiController::class, 'index']);
    Route::get('/barang/kategori', [MasterBarangApiController::class, 'kategori']);
    Route::get('/barang/{sku}', [MasterBarangApiController::class, 'show']);

    // --- Inbound ---
    Route::get('/inbound', [InboundApiController::class, 'index']);
    Route::post('/inbound', [InboundApiController::class, 'store']);
    Route::get('/inbound/{id}', [InboundApiController::class, 'show']);

    // --- Outbound ---
    Route::get('/outbound', [OutboundApiController::class, 'index']);
    Route::post('/outbound', [OutboundApiController::class, 'store']);
    Route::get('/outbound/{id}', [OutboundApiController::class, 'show']);
    Route::post('/outbound/{id}/picking-complete', [OutboundApiController::class, 'completePicking']);

    // --- Inventory / Kartu Stok ---
    Route::get('/inventory/kartu-stok', [InventoryApiController::class, 'kartuStokIndex']);
    Route::get('/inventory/kartu-stok/{sku}', [InventoryApiController::class, 'kartuStokDetail']);

    // --- Stock Opname ---
    Route::get('/stock-opname', [StockOpnameApiController::class, 'index']);
    Route::post('/stock-opname', [StockOpnameApiController::class, 'store']);
    Route::put('/stock-opname/{id}', [StockOpnameApiController::class, 'update']);
    Route::delete('/stock-opname/{id}', [StockOpnameApiController::class, 'destroy']);

    // --- Master Data (Supplier, Customer, Rack) ---
    Route::get('/suppliers', [MasterDataApiController::class, 'suppliers']);
    Route::get('/customers', [MasterDataApiController::class, 'customers']);
    Route::get('/rack-locations', [MasterDataApiController::class, 'rackLocations']);
});

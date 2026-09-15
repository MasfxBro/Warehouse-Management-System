<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\WmsApiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MasterBarangController;
use App\Http\Controllers\OutboundController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('signed:relative')->prefix('files')->group(function () {
        Route::get('/laporan-inventori', [LaporanController::class, 'exportInventori'])->name('api.reports.inventory');
        Route::get('/laporan-inbound', [LaporanController::class, 'exportInbound'])->name('api.reports.inbound');
        Route::get('/laporan-outbound', [LaporanController::class, 'exportOutbound'])->name('api.reports.outbound');
        Route::get('/barang/{sku}/label', [MasterBarangController::class, 'labelPdf'])->name('api.files.item-label');
        Route::get('/outbound/{id}/surat-jalan', [OutboundController::class, 'downloadSuratJalan'])->name('api.files.delivery-note');
    });
    Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:5,1');
    Route::middleware('api.token')->group(function () {
        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::get('/me', [AuthApiController::class, 'me']);
        Route::post('/student-identity', [AuthApiController::class, 'saveIdentity']);
        Route::delete('/student-identity', [AuthApiController::class, 'resetIdentity']);

        Route::get('/dashboard', [WmsApiController::class, 'dashboard']);
        Route::get('/report-links', [WmsApiController::class, 'reportLinks']);
        Route::get('/barang/kategori', [WmsApiController::class, 'categories']);
        Route::get('/barang', [WmsApiController::class, 'items']);
        Route::get('/barang/{sku}', [WmsApiController::class, 'item']);
        Route::get('/barang/{sku}/label-link', [WmsApiController::class, 'itemLabelLink']);
        Route::get('/suppliers', [WmsApiController::class, 'suppliers']);
        Route::get('/customers', [WmsApiController::class, 'customers']);
        Route::get('/rack-locations', [WmsApiController::class, 'racks']);
        Route::get('/rack-locations/{id}', [WmsApiController::class, 'rack']);
        Route::get('/rack-locations/{id}/photo', [WmsApiController::class, 'rackPhoto']);
        Route::get('/inbound', [WmsApiController::class, 'inbounds']);
        Route::get('/inbound-form-options', [WmsApiController::class, 'inboundOptions']);
        Route::get('/inbound/{id}', [WmsApiController::class, 'inbound']);
        Route::get('/outbound', [WmsApiController::class, 'outbounds']);
        Route::get('/outbound-form-options', [WmsApiController::class, 'outboundOptions']);
        Route::get('/outbound/{id}', [WmsApiController::class, 'outbound']);
        Route::get('/outbound/{id}/document-link', [WmsApiController::class, 'outboundDocumentLink']);
        Route::get('/stock-opname', [WmsApiController::class, 'stockOpnames']);

        Route::middleware('api.student.identity')->group(function () {
            Route::post('/suppliers', [WmsApiController::class, 'storeSupplier']);
            Route::post('/customers', [WmsApiController::class, 'storeCustomer']);
            Route::post('/units', [WmsApiController::class, 'storeUnit']);
            Route::post('/rack-locations/{id}/move-item', [WmsApiController::class, 'moveRackItem']);
            Route::post('/inbound', [WmsApiController::class, 'storeInbound']);
            Route::post('/outbound', [WmsApiController::class, 'storeOutbound']);
            Route::post('/stock-opname', [WmsApiController::class, 'storeStockOpname']);
            Route::post('/outbound/{id}/picking-complete', [WmsApiController::class, 'completePicking']);
        });
        Route::middleware('role:admin')->group(function () {
            Route::put('/suppliers/{id}', [WmsApiController::class, 'updateSupplier']);
            Route::put('/customers/{id}', [WmsApiController::class, 'updateCustomer']);
            Route::post('/rack-locations', [WmsApiController::class, 'storeRack']);
            Route::put('/rack-locations/{id}', [WmsApiController::class, 'updateRack']);
            Route::delete('/rack-locations/{id}', [WmsApiController::class, 'deleteRack']);
            Route::post('/rack-locations/{id}/photo', [WmsApiController::class, 'uploadRackPhoto']);
            Route::delete('/rack-locations/{id}/photo', [WmsApiController::class, 'deleteRackPhoto']);
            Route::get('/inventory/kartu-stok', [WmsApiController::class, 'inventory']);
            Route::get('/inventory/kartu-stok/{sku}', [WmsApiController::class, 'inventoryDetail']);
            Route::post('/inbound/{id}/cancel', [WmsApiController::class, 'cancelInbound']);
            Route::post('/outbound/{id}/cancel', [WmsApiController::class, 'cancelOutbound']);
            Route::get('/activity-logs', [WmsApiController::class, 'activityLogs']);
            Route::get('/practice-sessions', [WmsApiController::class, 'practiceSessions']);
            Route::post('/practice-sessions', [WmsApiController::class, 'storePracticeSession']);
            Route::post('/practice-sessions/{id}/close', [WmsApiController::class, 'closePracticeSession']);
        });
    });
});

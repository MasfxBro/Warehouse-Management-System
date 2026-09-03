<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentIdentityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\RackLocationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MasterBarangController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\LaporanController;

// Rute Tamu / Guest (Halaman Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

// Rute Terproteksi (Login Required & Check Identity)
Route::middleware(['auth', 'student.identity'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/student-identity', [StudentIdentityController::class, 'store'])->name('student-identity.store');
    Route::post('/student-identity/reset', [StudentIdentityController::class, 'reset'])->name('student-identity.reset');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard Utama (Akses: Admin & Siswa)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // MASTER DATA ROUTES
    Route::prefix('master-data')->name('master.')->group(function () {
        
        // Lokasi Rak
        Route::get('/rak', [RackLocationController::class, 'index'])->name('rak.index');
        Route::get('/rak/{id}', [RackLocationController::class, 'show'])->name('rak.show');
        Route::middleware('role:admin')->group(function () {
            Route::post('/rak', [RackLocationController::class, 'store'])->name('rak.store');
            Route::put('/rak/{id}', [RackLocationController::class, 'update'])->name('rak.update');
            Route::delete('/rak/{id}', [RackLocationController::class, 'destroy'])->name('rak.destroy');
            Route::post('/rak/{id}/pindah-barang', [RackLocationController::class, 'pindahBarang'])->name('rak.pindah-barang');
        });

        // Supplier
        Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
        Route::middleware('role:admin')->group(function () {
            Route::put('/supplier/{id}', [SupplierController::class, 'update'])->name('supplier.update');
        });

        // Customer (Pure Read-Only)
        Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');

        // Data Barang (Pure Read-Only + Detail, QR & Label PDF)
        Route::get('/barang', [MasterBarangController::class, 'index'])->name('barang.index');
        Route::get('/barang/{sku}', [MasterBarangController::class, 'show'])->name('barang.show');
        Route::get('/barang/{sku}/label-pdf', [MasterBarangController::class, 'labelPdf'])->name('barang.label-pdf');
    });

    // Transaksi Inbound (Akses: Admin & Siswa)
    Route::prefix('inbound')->name('inbound.')->group(function () {
        Route::get('/', [InboundController::class, 'index'])->name('index');
        Route::get('/create', [InboundController::class, 'create'])->name('create');
        Route::post('/', [InboundController::class, 'store'])->name('store');
        Route::post('/supplier-ajax', [InboundController::class, 'storeSupplierAjax'])->name('supplier.ajax');
        Route::get('/{id}', [InboundController::class, 'show'])->name('show');
    });

    // Transaksi Outbound (Akses: Admin & Siswa)
    Route::prefix('outbound')->name('outbound.')->group(function () {
        Route::get('/', [OutboundController::class, 'index'])->name('index');
        Route::get('/create', [OutboundController::class, 'create'])->name('create');
        Route::post('/', [OutboundController::class, 'store'])->name('store');
        Route::post('/customer-ajax', [OutboundController::class, 'storeCustomerAjax'])->name('customer.ajax');
        Route::get('/{id}', [OutboundController::class, 'show'])->name('show');
        Route::get('/{id}/picking-list', [OutboundController::class, 'showPickingList'])->name('picking-list');
        Route::post('/{id}/picking-complete', [OutboundController::class, 'completePicking'])->name('picking-complete');
        Route::get('/{id}/surat-jalan', [OutboundController::class, 'downloadSuratJalan'])->name('surat-jalan');
    });

    // System Log Engine (Akses: HANYA Admin / Guru)
    Route::middleware('role:admin')->prefix('logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('logs.index');
    });

    // INVENTORY — Kartu Stok & Stock Opname
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/kartu-stok', [InventoryController::class, 'kartuStokIndex'])->name('kartu-stok.index');
        Route::get('/kartu-stok/{sku}', [InventoryController::class, 'kartuStokDetail'])->name('kartu-stok.detail');

        // Stock Opname — Tambah & Lihat saja (edit/hapus via modal Detail di index)
        Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('stock-opname.index');
        Route::get('/stock-opname/create', [StockOpnameController::class, 'create'])->name('stock-opname.create');
        Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('stock-opname.store');
    });

    // LAPORAN & EXPORT
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/inventori/export', [LaporanController::class, 'exportInventori'])->name('inventori.export');
        Route::get('/inbound/export', [LaporanController::class, 'exportInbound'])->name('inbound.export');
        Route::get('/outbound/export', [LaporanController::class, 'exportOutbound'])->name('outbound.export');
    });

});

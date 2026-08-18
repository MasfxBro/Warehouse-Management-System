<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterBarangController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RackLocationController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\KartuStokController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Master Data (Admin & Manager)
    Route::middleware('role:admin,manager')->prefix('master')->name('master.')->group(function () {
        Route::resource('barang', MasterBarangController::class)->parameters(['barang' => 'sku']);
        Route::resource('supplier', SupplierController::class);
        Route::resource('customer', CustomerController::class);
        Route::resource('rack', RackLocationController::class);
    });
    
    // Transaksi Inbound
    Route::resource('inbound', InboundController::class);
    Route::get('inbound/{id}/barcode', [InboundController::class, 'barcode'])->name('inbound.barcode');
    
    // Transaksi Outbound
    Route::resource('outbound', OutboundController::class);
    Route::get('outbound/{id}/picking-list', [OutboundController::class, 'pickingList'])->name('outbound.picking-list');
    Route::get('outbound/{id}/surat-jalan', [OutboundController::class, 'suratJalan'])->name('outbound.surat-jalan');
    
    // Inventory
    Route::get('/inventory/kartu-stok', [KartuStokController::class, 'index'])->name('inventory.kartu-stok');
    Route::get('/inventory/kartu-stok/export/excel', [KartuStokController::class, 'exportExcel'])->name('inventory.kartu-stok.export-excel');
    Route::get('/inventory/kartu-stok/export/pdf', [KartuStokController::class, 'exportPdf'])->name('inventory.kartu-stok.export-pdf');
    Route::get('/inventory/kartu-stok/{sku}', [KartuStokController::class, 'show'])->name('inventory.kartu-stok.show');
    
    Route::middleware('role:admin,manager')->prefix('inventory/stock-opname')->name('inventory.stock-opname.')->group(function () {
        Route::get('/', [StockOpnameController::class, 'index'])->name('index');
        Route::get('/create', [StockOpnameController::class, 'create'])->name('create');
        Route::post('/', [StockOpnameController::class, 'store'])->name('store');
        Route::get('/export/excel', [StockOpnameController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export/pdf', [StockOpnameController::class, 'exportPdf'])->name('export-pdf');
    });
    
    // Laporan (Admin & Manager)
    Route::middleware('role:admin,manager')->prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/inventory/export', [LaporanController::class, 'exportInventory'])->name('inventory.export');
        Route::get('/inbound/export', [LaporanController::class, 'exportInbound'])->name('inbound.export');
        Route::get('/outbound/export', [LaporanController::class, 'exportOutbound'])->name('outbound.export');
    });
    
    // Users (Admin only)
    Route::middleware('role:admin')->resource('users', UserController::class);
});

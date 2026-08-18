<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OutboundController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('outbound')->group(function () {

    Route::get('/', [OutboundController::class, 'index'])
        ->name('outbound.index');

    Route::get('/create', [OutboundController::class, 'create'])
        ->name('outbound.create');

    Route::post('/', [OutboundController::class, 'store'])
        ->name('outbound.store');

    Route::get('/{outboundId}/picking-list', [OutboundController::class, 'showPickingList'])
        ->name('outbound.picking-list');

    Route::get('/{outboundId}/surat-jalan', [OutboundController::class, 'downloadSuratJalan'])
        ->name('outbound.surat-jalan');
});

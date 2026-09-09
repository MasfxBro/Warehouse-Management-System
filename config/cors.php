<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini mengizinkan request dari Flutter Web (Chrome), Ngrok,
    | maupun origin lain agar tidak diblokir browser saat development.
    |
    */

    // Endpoint yang terkena aturan CORS
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Izinkan semua origin (termasuk URL Ngrok yang berubah setiap sesi)
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    // Izinkan semua header — termasuk 'ngrok-skip-browser-warning'
    // dan 'Authorization' untuk Sanctum Bearer Token
    'allowed_headers' => [
        'Content-Type',
        'Accept',
        'Authorization',
        'X-Requested-With',
        'ngrok-skip-browser-warning',
    ],

    'exposed_headers' => [],

    'max_age' => 86400,

    // PENTING: harus false jika allowed_origins = ['*']
    // Jika pakai Sanctum cookie-based auth, ubah ke true dan
    // set allowed_origins ke URL spesifik (bukan wildcard).
    'supports_credentials' => false,

];

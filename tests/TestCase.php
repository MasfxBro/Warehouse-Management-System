<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $database = $app->make('config')->get('database');
        $isSafeTestDatabase = $app->environment('testing')
            && ($database['default'] ?? null) === 'sqlite'
            && ($database['connections']['sqlite']['database'] ?? null) === ':memory:';

        if (! $isSafeTestDatabase) {
            throw new RuntimeException(
                'Test dibatalkan: koneksi wajib SQLite :memory:. Jalankan "php artisan optimize:clear" lalu ulangi test.'
            );
        }

        return $app;
    }
}

<?php

use App\Services\DemoDataRepairService;
use App\Services\WarehouseIntegrityService;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;

Artisan::command('wms:audit', function (WarehouseIntegrityService $integrity) {
    $result = $integrity->audit();
    $this->table(['Metrik', 'Jumlah'], collect($result['metrics'])->map(fn ($value, $key) => [$key, $value]));

    if ($result['issues'] === []) {
        $this->info('Integritas stok, kapasitas rak, dan harga: OK.');

        return Command::SUCCESS;
    }

    $this->error(count($result['issues']).' masalah integritas ditemukan.');
    $this->table(['Jenis', 'Referensi', 'Detail'], $result['issues']);

    return Command::FAILURE;
})->purpose('Audit integritas data dan saldo WMS tanpa mengubah data.');

Artisan::command('wms:demo-repair {--force}', function (DemoDataRepairService $repair) {
    if (! app()->environment('local', 'testing')) {
        $this->error('Perintah ini hanya boleh dijalankan pada environment local/testing.');

        return Command::FAILURE;
    }
    if (! $this->option('force')) {
        $this->warn('Tambahkan --force untuk menjalankan penyesuaian data demo yang tercatat dan dapat diaudit.');

        return Command::FAILURE;
    }

    $result = $repair->repair();
    $this->info("Penyesuaian stok: {$result['adjusted_units']} unit; relokasi rak: {$result['moved_units']} unit.");
    if ($result['adjustment_number']) {
        $this->line("Dokumen penyesuaian: {$result['adjustment_number']}");
    }

    return Command::SUCCESS;
})->purpose('Perbaiki saldo negatif dan rak melebihi kapasitas pada data demo lama.');

Artisan::command('wms:demo-reset {--force}', function () {
    if (! app()->environment('local', 'testing')) {
        $this->error('Reset demo hanya boleh dijalankan pada environment local/testing.');

        return Command::FAILURE;
    }
    if (! $this->option('force')) {
        $this->warn('Perintah ini menghapus seluruh data. Tambahkan --force hanya saat benar-benar ingin mereset demo.');

        return Command::FAILURE;
    }

    $exitCode = $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
    if ($exitCode !== Command::SUCCESS) {
        return $exitCode;
    }

    return $this->call('wms:audit');
})->purpose('Reset lokal ke data demo deterministik lalu jalankan audit integritas.');

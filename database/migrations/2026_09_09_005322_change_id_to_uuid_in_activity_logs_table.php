<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Aktifkan ekstensi pgcrypto untuk generate UUID di PostgreSQL
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');

        // 2. Lepaskan constraint primary key lama jika ada
        DB::statement('ALTER TABLE activity_logs DROP CONSTRAINT IF EXISTS activity_logs_pkey CASCADE;');

        // 3. HAPUS DEFAULT LAMA (auto-increment sequence) agar tidak bentrok dengan UUID
        DB::statement('ALTER TABLE activity_logs ALTER COLUMN id DROP DEFAULT;');

        // 4. Ubah tipe data kolom id ke UUID dan konversi data lama
        DB::statement('ALTER TABLE activity_logs ALTER COLUMN id TYPE uuid USING gen_random_uuid();');

        // 5. Pasang kembali Primary Key pada kolom id
        DB::statement('ALTER TABLE activity_logs ADD PRIMARY KEY (id);');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE activity_logs DROP CONSTRAINT IF EXISTS activity_logs_pkey CASCADE;');
        DB::statement('ALTER TABLE activity_logs ALTER COLUMN id TYPE bigint USING id::text::bigint;');
        DB::statement('ALTER TABLE activity_logs ADD PRIMARY KEY (id);');
    }
};
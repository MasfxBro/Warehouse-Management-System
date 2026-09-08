<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_counters', function (Blueprint $table) {
            $table->id();
            $table->string('Document_Type', 20);
            $table->date('Document_Date');
            $table->unsignedInteger('Last_Number')->default(0);
            $table->timestamps();
            $table->unique(['Document_Type', 'Document_Date']);
        });

        Schema::create('practice_sessions', function (Blueprint $table) {
            $table->uuid('Practice_Session_ID')->primary();
            $table->string('Nama', 150);
            $table->string('Kelas', 100)->nullable();
            $table->date('Tanggal');
            $table->string('Status', 20)->default('active');
            $table->foreignId('Created_By')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('Opened_At')->nullable();
            $table->timestamp('Closed_At')->nullable();
            $table->timestamps();
            $table->index(['Status', 'Tanggal']);
        });

        foreach (['inbound_transactions', 'outbound_transactions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('transaction_status', 20)->default('active');
                $table->timestamp('Cancelled_At')->nullable();
                $table->foreignId('Cancelled_By')->nullable()->constrained('users')->nullOnDelete();
                $table->text('Cancellation_Reason')->nullable();
                $table->uuid('Practice_Session_ID')->nullable();
                $table->foreign('Practice_Session_ID')->references('Practice_Session_ID')->on('practice_sessions')->nullOnDelete();
                $table->index(['transaction_status', 'Tanggal']);
                $table->index('Practice_Session_ID');
            });
        }

        foreach (['activity_logs', 'stock_opnames'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->uuid('Practice_Session_ID')->nullable();
                $table->foreign('Practice_Session_ID')->references('Practice_Session_ID')->on('practice_sessions')->nullOnDelete();
                $table->index('Practice_Session_ID');
            });
        }

        $sessionId = (string) Str::uuid();
        $now = now();
        DB::table('practice_sessions')->insert([
            'Practice_Session_ID' => $sessionId,
            'Nama' => 'Sesi Demo Utama',
            'Kelas' => 'Demo WMS',
            'Tanggal' => today(),
            'Status' => 'active',
            'Opened_At' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (['inbound_transactions', 'outbound_transactions', 'activity_logs', 'stock_opnames'] as $tableName) {
            DB::table($tableName)->update(['Practice_Session_ID' => $sessionId]);
        }

        $this->seedCounters('RSI', 'inbound_transactions', 'No_Receiving');
        $this->seedCounters('SJ', 'outbound_transactions', 'No_Shipping');
    }

    private function seedCounters(string $type, string $table, string $numberColumn): void
    {
        DB::table($table)
            ->select(['Tanggal', $numberColumn])
            ->orderBy('Tanggal')
            ->get()
            ->groupBy('Tanggal')
            ->each(function ($rows, $date) use ($type, $numberColumn): void {
                $max = $rows->map(function ($row) use ($numberColumn): int {
                    preg_match('/(\d{4})$/', $row->{$numberColumn}, $matches);

                    return isset($matches[1]) ? (int) $matches[1] : 0;
                })->max();

                DB::table('document_counters')->insert([
                    'Document_Type' => $type,
                    'Document_Date' => $date,
                    'Last_Number' => max($max, $rows->count()),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        foreach (['activity_logs', 'stock_opnames'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['Practice_Session_ID']);
                $table->dropColumn('Practice_Session_ID');
            });
        }

        foreach (['inbound_transactions', 'outbound_transactions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['Practice_Session_ID']);
                $table->dropForeign(['Cancelled_By']);
                $table->dropColumn(['transaction_status', 'Cancelled_At', 'Cancelled_By', 'Cancellation_Reason', 'Practice_Session_ID']);
            });
        }

        Schema::dropIfExists('practice_sessions');
        Schema::dropIfExists('document_counters');
    }
};

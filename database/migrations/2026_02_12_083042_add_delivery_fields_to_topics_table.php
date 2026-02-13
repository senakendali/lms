<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $table) {

            // Tipe materi
            $table->enum('delivery_type', ['video', 'live', 'hybrid'])
                  ->default('video')
                  ->after('title');

            // Khusus video (berapa persen dianggap selesai)
            $table->unsignedInteger('pass_progress_pct')
                  ->default(90)
                  ->after('delivery_type');

            // Khusus live / hybrid
            $table->timestamp('session_at')
                  ->nullable()
                  ->after('pass_progress_pct');

        });
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_type',
                'pass_progress_pct',
                'session_at',
            ]);
        });
    }
};

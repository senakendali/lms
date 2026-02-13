<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            // HTML dari Quill (outline/subtopics)
            $table->longText('subtopics')->nullable()->after('title');

            // optional kalau lu pengen strict ordering per module
            // $table->index(['module_id', 'order']);
            // $table->unique(['module_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn('subtopics');
        });
    }
};

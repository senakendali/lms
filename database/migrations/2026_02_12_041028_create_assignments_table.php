<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();

            $table->string('title');
            $table->longText('description')->nullable(); // bisa HTML dari editor kalau mau

            // opsional tapi kepake banget
            $table->unsignedInteger('max_score')->default(100);
            $table->timestamp('due_at')->nullable();
            $table->boolean('is_published')->default(false);

            // siapa yang buat (instructor/admin)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedInteger('order')->default(0);

            $table->timestamps();

            $table->index(['topic_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};

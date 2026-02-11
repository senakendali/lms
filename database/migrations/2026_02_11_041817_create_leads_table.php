<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->nullable(); // Website, WA, IG, etc

            $table->enum('status', ['new', 'contacted', 'interested', 'converted', 'rejected'])
                  ->default('new');

            $table->text('notes')->nullable();
            $table->timestamps();

            // Optional indexes
            $table->index('status');
            $table->index('phone');

            // Nullable unique aman (MySQL: multiple NULL allowed)
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};

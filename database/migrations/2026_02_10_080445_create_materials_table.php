<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // database/migrations/xxxx_create_materials_table.php
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();

            $table->string('title');

            // video | file | link
            $table->enum('type', ['video', 'file', 'link']);

            // untuk video (Google Drive)
            $table->string('drive_id')->nullable();

            // untuk link eksternal
            $table->text('url')->nullable();

            // untuk file upload (pdf, ppt, dll)
            $table->string('file_path')->nullable();

            $table->integer('order')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};

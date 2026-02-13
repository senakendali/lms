<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('video_progress', function (Blueprint $table) {
      $table->id();

      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
      $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
      $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();

      // progress video
      $table->unsignedInteger('watched_seconds')->default(0);
      $table->unsignedInteger('duration_seconds')->default(0); // isi kalau lu tau durasi
      $table->unsignedTinyInteger('progress_pct')->default(0); // 0..100

      $table->timestamp('last_watched_at')->nullable();

      $table->timestamps();

      $table->unique(['user_id', 'material_id']); // 1 user 1 progress per video
      $table->index(['user_id', 'course_id', 'topic_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('video_progress');
  }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('topic_progress', function (Blueprint $table) {
      $table->id();

      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
      $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();

      // not_started | in_progress | done
      $table->string('status')->default('not_started');

      // untuk live/hybrid: attendance/manual completion
      $table->timestamp('started_at')->nullable();
      $table->timestamp('completed_at')->nullable();

      $table->timestamps();

      $table->unique(['user_id', 'topic_id']); // 1 student 1 progress per topic
      $table->index(['user_id', 'course_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('topic_progress');
  }
};

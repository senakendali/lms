<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();

            // Jawaban: bisa text/link/file (kita fleksibel)
            $table->longText('answer_text')->nullable();
            $table->string('answer_url')->nullable();
            $table->string('answer_file_path')->nullable();

            // status submit
            $table->timestamp('submitted_at')->nullable();
            $table->string('status')->default('draft'); 
            // draft | submitted | graded | returned (opsional)

            // penilaian
            $table->unsignedInteger('score')->nullable(); // 0..max_score
            $table->longText('instructor_note')->nullable(); // komentar/feedback ringkas di level nilai
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();

            $table->timestamps();

            // 1 student 1 submission per assignment (kalau mau multi-attempt, ini kita ubah nanti)
            $table->unique(['assignment_id', 'student_id']);
            $table->index(['assignment_id', 'status']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};

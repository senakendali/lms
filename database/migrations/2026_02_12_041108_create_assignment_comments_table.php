<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignment_comments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('submission_id')->constrained('assignment_submissions')->cascadeOnDelete();

            // siapa yang komen (student/instructor)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // reply hierarchy
            $table->foreignId('parent_id')->nullable()->constrained('assignment_comments')->cascadeOnDelete();

            $table->longText('body'); // boleh HTML/plaintext, terserah UI lo

            // kecil2 tapi berguna
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();

            $table->timestamps();

            $table->index(['submission_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_comments');
    }
};

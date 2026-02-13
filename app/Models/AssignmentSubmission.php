<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',

        'answer_text',
        'answer_url',
        'answer_file_path',

        'submitted_at',
        'status',

        'score',
        'instructor_note',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // helper scopes (opsional tapi kepake)
    public function scopeSubmitted($q)
    {
        return $q->where('status', 'submitted');
    }

    public function scopeGraded($q)
    {
        return $q->where('status', 'graded');
    }
}

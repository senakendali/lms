<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'topic_id',
        'title',
        'description',
        'max_score',
        'due_at',
        'is_published',
        'created_by',
        'order',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function topic()
    {
        return $this->belongsTo(\App\Models\Topic::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function submissions()
    {
        return $this->hasMany(\App\Models\AssignmentSubmission::class);
    }


    public function mySubmission($studentId)
    {
        return $this->hasOne(\App\Models\AssignmentSubmission::class)->where('student_id', $studentId);
    }


}

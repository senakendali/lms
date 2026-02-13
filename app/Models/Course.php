<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Course extends Model
{
    protected $fillable = [
        'title',
        'description',
        'instructor_id',
        'is_active',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class)
            ->where('role', 'student')
            ->withPivot(['status', 'completed_at'])
            ->withTimestamps();
    }

    /**
     * Shortcut: semua topics dalam course ini
     * Course -> Modules -> Topics
     */
    public function topics(): HasManyThrough
    {
        // topics.module_id -> modules.id
        // modules.course_id -> courses.id
        return $this->hasManyThrough(
            Topic::class,
            Module::class,
            'course_id',   // FK on modules table...
            'module_id',   // FK on topics table...
            'id',          // Local key on courses table...
            'id'           // Local key on modules table...
        )->orderBy('topics.order');
    }

    /**
     * Shortcut: semua assignments dalam course ini
     * Course -> Modules -> Topics -> Assignments
     *
     * Trik: hasManyThrough ke Topic dulu, lalu join ke assignments.
     * Ini bukan relasi "native" Eloquent 2-hop, tapi cukup buat query/count.
     */
    public function assignments()
    {
        // mulai dari topics dalam course
        return Assignment::query()
            ->whereHas('topic.module', function ($q) {
                $q->where('course_id', $this->id);
            });
    }
}

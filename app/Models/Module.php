<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Module extends Model
{
    protected $fillable = ['course_id', 'title', 'learning_objectives', 'order'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function topics()
    {
        return $this->hasMany(Topic::class)->orderBy('order');
    }
}


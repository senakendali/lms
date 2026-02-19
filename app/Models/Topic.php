<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Topic extends Model
{
    protected $fillable = [
        'module_id',
        'title',
        'order',
        'delivery_type',
        'subtopics',
        'pass_progress_pct',
        'session_at',
    ];


    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class)->orderBy('order');
    }

    public function assignments()
    {
        return $this->hasMany(\App\Models\Assignment::class)->orderBy('order');
    }

}



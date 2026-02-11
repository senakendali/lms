<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Topic extends Model
{
    protected $fillable = ['module_id', 'title', 'order'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class)->orderBy('order');
    }
}



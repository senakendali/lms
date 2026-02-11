<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'source',
        'status',
        'notes',
    ];

    public const STATUSES = ['new', 'contacted', 'interested', 'converted', 'rejected'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoProgress extends Model
{
  protected $table = 'video_progress';

  protected $fillable = [
    'user_id','course_id','topic_id','material_id',
    'watched_seconds','duration_seconds','progress_pct','last_watched_at'
  ];

  protected $casts = [
    'last_watched_at' => 'datetime',
  ];
}

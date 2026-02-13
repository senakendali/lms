<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicProgress extends Model
{
  protected $table = 'topic_progress';

  protected $fillable = [
    'user_id','course_id','topic_id','status','started_at','completed_at'
  ];

  protected $casts = [
    'started_at' => 'datetime',
    'completed_at' => 'datetime',
  ];
}

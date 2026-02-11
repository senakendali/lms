<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    protected $fillable = [
        'topic_id',
        'title',
        'type',
        'drive_id',
        'url',
        'file_path',
        'order',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function driveEmbedUrl()
    {
        return $this->drive_id
            ? "https://drive.google.com/file/d/{$this->drive_id}/preview"
            : null;
    }

    public function fileUrl()
    {
        return $this->file_path
            ? asset('storage/'.$this->file_path)
            : null;
    }
}


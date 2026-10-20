<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingRecording extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'title',
        'file_name',
        'storage_disk',
        'file_path',
        'file_size',
        'duration_seconds',
        'mime_type',
        'status',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'duration_seconds' => 'integer',
        'metadata' => 'array',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->duration_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}


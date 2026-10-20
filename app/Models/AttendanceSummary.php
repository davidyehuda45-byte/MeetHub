<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'user_id',
        'guest_identifier',
        'total_seconds',
        'first_join_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_seconds' => 'integer',
            'first_join_at' => 'datetime',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = floor($this->total_seconds / 60);
        $seconds = $this->total_seconds % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}


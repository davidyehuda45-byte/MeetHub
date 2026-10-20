<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'user_id',
        'guest_name',
        'session_token',
        'joined_at',
        'left_at',
        'last_heartbeat_at',
        'platform',
        'device_info',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'last_heartbeat_at' => 'datetime',
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

    public function getParticipantNameAttribute(): string
    {
        return $this->user ? $this->user->name : ($this->guest_name ?? 'Tamu');
    }
}


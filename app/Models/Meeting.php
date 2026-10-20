<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'title',
        'description',
        'room_code',
        'jitsi_room_name',
        'password_hash',
        'scheduled_at',
        'duration_minutes',
        'status',
        'lobby_enabled',
        'allow_guests',
        'start_muted',
        'recurrence_rule',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_minutes' => 'integer',
            'lobby_enabled' => 'boolean',
            'allow_guests' => 'boolean',
            'start_muted' => 'boolean',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(MeetingInvitation::class);
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function attendanceSummaries(): HasMany
    {
        return $this->hasMany(AttendanceSummary::class);
    }

    public function note(): HasOne
    {
        return $this->hasOne(MeetingNote::class);
    }

    public function actionItems(): HasMany
    {
        return $this->hasMany(ActionItem::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(MeetingFile::class);
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(MeetingRecording::class);
    }

    public function hasPassword(): bool
    {
        return !empty($this->password_hash);
    }

    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function getJoinUrlAttribute(): string
    {
        return url('/m/' . $this->room_code);
    }
}


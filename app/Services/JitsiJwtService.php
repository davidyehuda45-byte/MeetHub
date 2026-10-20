<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;

class JitsiJwtService
{
    protected string $domain;
    protected string $appId;
    protected string $appSecret;
    protected string $algorithm;
    protected int $expiryHours;

    public function __construct()
    {
        $this->domain = \App\Models\Setting::get('jitsi_domain') ?: config('jitsi.domain', 'meet.jit.si');
        $this->appId = config('jitsi.app_id', 'meethub');
        $this->appSecret = config('jitsi.app_secret', 'meethub_super_secret_jwt_key_2026_dev_32bytes');
        $this->algorithm = config('jitsi.jwt_algo', 'HS256');
        $this->expiryHours = (int) config('jitsi.jwt_expiry_hours', 4);
    }

    /**
     * Check if the configured domain is the public meet.jit.si server.
     */
    public function isPublicDomain(): bool
    {
        return str_contains($this->domain, 'meet.jit.si') 
            || str_contains($this->domain, 'ffmuc.net') 
            || str_contains($this->domain, 'fairmeeting.net')
            || str_contains($this->domain, 'systemli.org');
    }

    /**
     * Generate a signed Jitsi JWT token for a meeting participant or guest.
     */
    public function generateToken(Meeting $meeting, ?User $user, ?string $guestName = null, bool $forceModerator = false): string
    {
        $now = time();
        $isHost = false;

        if ($user) {
            $isHost = ($user->id === $meeting->host_id) || $user->isAdmin();
            $displayName = $user->name;
            $email = $user->email;
            $userId = (string) $user->id;
            $avatar = $user->avatar_path ? url($user->avatar_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=4f46e5&color=fff';
        } else {
            $displayName = trim($guestName ?? 'Tamu') ?: 'Tamu';
            $email = null;
            $userId = 'guest_' . Str::random(10);
            $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=64748b&color=fff';
        }

        $isModerator = $forceModerator || $isHost;

        $payload = [
            'aud' => $this->appId,
            'iss' => $this->appId,
            'sub' => $this->domain,
            'room' => $meeting->jitsi_room_name,
            'iat' => $now,
            'nbf' => $now - 10,
            'exp' => $now + ($this->expiryHours * 3600),
            'context' => [
                'user' => [
                    'id' => $userId,
                    'name' => $displayName,
                    'email' => $email,
                    'avatar' => $avatar,
                    'moderator' => $isModerator,
                ],
                'features' => [
                    'livestreaming' => false,
                    'recording' => false,
                    'transcription' => false,
                ],
            ],
        ];

        return JWT::encode($payload, $this->appSecret, $this->algorithm);
    }

    /**
     * Decode and verify a Jitsi JWT token.
     */
    public function decodeToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->appSecret, $this->algorithm));
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getDomain(): string
    {
        return $this->domain;
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\Meeting;
use App\Models\MeetingRecording;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JitsiWebhookController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Handle incoming webhooks from Jitsi Meet / Prosody server.
     */
    public function handle(Request $request): JsonResponse
    {
        $secret = config('jitsi.webhook_secret');

        if (!$this->verifySignature($request, $secret)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tanda tangan webhook atau token tidak valid.',
            ], 401);
        }

        $payload = $request->isJson() ? $request->json()->all() : $request->all();
        if (empty($payload)) {
            $payload = json_decode($request->getContent(), true) ?: [];
        }

        $event = $payload['event'] ?? $payload['type'] ?? null;
        $roomName = $payload['room_name'] ?? $payload['room'] ?? null;

        if (!$event || !$roomName) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payload webhook tidak lengkap (event dan room_name diperlukan).',
            ], 422);
        }

        $meeting = Meeting::where('jitsi_room_name', $roomName)
            ->orWhere('room_code', $roomName)
            ->first();

        if (!$meeting) {
            return response()->json([
                'status' => 'ignored',
                'message' => "Ruang rapat {$roomName} tidak terdaftar di MeetHub.",
            ], 200);
        }

        switch ($event) {
            case 'room_created':
                if ($meeting->status === 'scheduled') {
                    $meeting->update([
                        'status' => 'ongoing',
                        'started_at' => now(),
                    ]);
                }
                AuditLog::log('jitsi_webhook_room_created', Meeting::class, $meeting->id, [
                    'room_name' => $roomName,
                ]);
                break;

            case 'room_destroyed':
                if ($meeting->status === 'ongoing') {
                    $meeting->update([
                        'status' => 'completed',
                        'ended_at' => now(),
                    ]);

                    // Close any open attendance sessions
                    $openSessions = AttendanceSession::where('meeting_id', $meeting->id)
                        ->whereNull('left_at')
                        ->get();

                    foreach ($openSessions as $session) {
                        $session->update([
                            'left_at' => now(),
                            'last_heartbeat_at' => now(),
                        ]);
                        $this->attendanceService->updateSummaryAfterEvent($session);
                    }
                }
                AuditLog::log('jitsi_webhook_room_destroyed', Meeting::class, $meeting->id, [
                    'room_name' => $roomName,
                ]);
                break;

            case 'participant_joined':
                $pData = $payload['participant'] ?? [];
                $user = null;

                if (!empty($pData['email'])) {
                    $user = User::where('email', strtolower($pData['email']))->first();
                } elseif (!empty($pData['id']) && is_numeric($pData['id'])) {
                    $user = User::find($pData['id']);
                }

                $guestName = $user ? null : ($pData['name'] ?? 'Tamu Jitsi');

                $session = $this->attendanceService->recordJoin(
                    $meeting,
                    $user,
                    $guestName,
                    'jitsi_sfu',
                    'Jitsi Event Webhook'
                );

                AuditLog::log('jitsi_webhook_participant_joined', AttendanceSession::class, $session->id, [
                    'room_name' => $roomName,
                    'participant' => $pData,
                ]);
                break;

            case 'participant_left':
                $pData = $payload['participant'] ?? [];
                $user = null;

                if (!empty($pData['email'])) {
                    $user = User::where('email', strtolower($pData['email']))->first();
                } elseif (!empty($pData['id']) && is_numeric($pData['id'])) {
                    $user = User::find($pData['id']);
                }

                $guestName = $user ? null : ($pData['name'] ?? 'Tamu Jitsi');

                $openSession = AttendanceSession::where('meeting_id', $meeting->id)
                    ->whereNull('left_at')
                    ->when($user, fn($q) => $q->where('user_id', $user->id))
                    ->when(!$user, fn($q) => $q->where('guest_name', $guestName))
                    ->latest('joined_at')
                    ->first();

                if ($openSession) {
                    $openSession->update([
                        'left_at' => now(),
                        'last_heartbeat_at' => now(),
                    ]);
                    $this->attendanceService->updateSummaryAfterEvent($openSession);

                    AuditLog::log('jitsi_webhook_participant_left', AttendanceSession::class, $openSession->id, [
                        'room_name' => $roomName,
                    ]);
                }
                break;

            case 'recording_ready':
            case 'recording_uploaded':
                $recData = $payload['recording'] ?? [];
                $filePath = $recData['file_path'] ?? $recData['path'] ?? ('recordings/' . $meeting->room_code . '_' . time() . '.mp4');
                $fileName = $recData['file_name'] ?? basename($filePath);
                $fileSize = (int) ($recData['file_size'] ?? $recData['size'] ?? 0);
                $duration = (int) ($recData['duration'] ?? 0);
                $disk = $recData['storage_disk'] ?? 'local';

                $recording = MeetingRecording::create([
                    'meeting_id' => $meeting->id,
                    'title' => $recData['title'] ?? ('Rekaman ' . $meeting->title),
                    'file_name' => $fileName,
                    'storage_disk' => $disk,
                    'file_path' => $filePath,
                    'file_size' => $fileSize,
                    'duration_seconds' => $duration,
                    'mime_type' => $recData['mime_type'] ?? 'video/mp4',
                    'status' => 'ready',
                    'metadata' => $recData,
                ]);

                AuditLog::log('jitsi_webhook_recording_ready', MeetingRecording::class, $recording->id, [
                    'meeting_id' => $meeting->id,
                    'file_name' => $fileName,
                ]);
                break;
        }

        return response()->json([
            'status' => 'success',
            'event' => $event,
            'meeting_id' => $meeting->id,
            'message' => 'Event Webhook berhasil diproses.',
        ]);
    }

    /**
     * Verify incoming signature or bearer token against config.
     */
    protected function verifySignature(Request $request, ?string $secret): bool
    {
        if (empty($secret)) {
            return false;
        }

        // 1. Direct Bearer token or X-Jitsi-Token header
        $bearerToken = $request->bearerToken() ?? $request->header('X-Jitsi-Token');
        if ($bearerToken && hash_equals($secret, $bearerToken)) {
            return true;
        }

        // 2. HMAC-SHA256 signature (e.g. X-Jitsi-Signature: sha256=abcdef...)
        $signatureHeader = $request->header('X-Jitsi-Signature');
        if ($signatureHeader) {
            $expectedSignature = hash_hmac('sha256', $request->getContent(), $secret);
            $providedSignature = str_starts_with($signatureHeader, 'sha256=')
                ? substr($signatureHeader, 7)
                : $signatureHeader;

            return hash_equals($expectedSignature, $providedSignature);
        }

        return false;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Meeting;
use App\Models\MeetingInvitation;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MeetingInvitationController extends Controller
{
    /**
     * Invite participant via email.
     */
    public function invite(Request $request, string $code): JsonResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower($validated['email']);
        $existingUser = User::where('email', $email)->first();

        $token = Str::random(32);

        $invitation = MeetingInvitation::updateOrCreate(
            [
                'meeting_id' => $meeting->id,
                'email' => $email,
            ],
            [
                'user_id' => $existingUser?->id,
                'status' => 'pending',
                'token' => $token,
            ]
        );

        if ($existingUser) {
            Notification::create([
                'user_id' => $existingUser->id,
                'type' => 'meeting_invitation',
                'data' => [
                    'meeting_id' => $meeting->id,
                    'title' => $meeting->title,
                    'room_code' => $meeting->room_code,
                    'inviter' => Auth::user()?->name ?? 'Host',
                    'scheduled_at' => $meeting->scheduled_at->toIso8601String(),
                ],
            ]);
        }

        AuditLog::log('invitation_created', MeetingInvitation::class, $invitation->id, [
            'email' => $email,
            'meeting' => $meeting->title,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Undangan berhasil dikirim ke {$email}",
            'invitation' => $invitation,
        ]);
    }
}


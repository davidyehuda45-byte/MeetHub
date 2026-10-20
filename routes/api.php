<?php

use App\Http\Controllers\ActionItemController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingFileController;
use App\Http\Controllers\MeetingInvitationController;
use App\Http\Controllers\MeetingJoinController;
use App\Http\Controllers\MeetingNoteController;
use App\Models\Meeting;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::prefix('v1')->group(function () {
    // Health Check
    Route::get('/health', function () {
        $dbStatus = 'ok';
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbStatus = 'disconnected: ' . $e->getMessage();
        }

        $storageStatus = 'ok';
        try {
            Storage::disk('local')->put('_health_check.tmp', 'ok');
            Storage::disk('local')->delete('_health_check.tmp');
        } catch (\Throwable $e) {
            $storageStatus = 'unwritable: ' . $e->getMessage();
        }

        $isHealthy = ($dbStatus === 'ok' && $storageStatus === 'ok');

        return response()->json([
            'status' => $isHealthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'database' => $dbStatus,
            'storage' => $storageStatus,
            'jitsi_domain' => \App\Models\Setting::get('jitsi_domain', config('jitsi.domain', 'meet.jit.si')),
            'version' => '1.0.0',
        ], $isHealthy ? 200 : 503);
    });

    // Auth
    Route::post('/auth/login', [AuthController::class, 'apiLogin']);
    Route::post('/auth/register', [AuthController::class, 'apiRegister']);

    // Public / Guest Join & Attendance Heartbeats
    Route::post('/meetings/{code}/join', [MeetingJoinController::class, 'apiJoin']);
    Route::post('/meetings/attendance/heartbeat', [MeetingJoinController::class, 'heartbeat']);
    Route::post('/meetings/attendance/leave', [MeetingJoinController::class, 'leave']);

    // Authenticated Mobile API
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'apiLogout']);
        Route::get('/me', [AuthController::class, 'apiMe']);

        // Meetings for Mobile
        Route::get('/meetings', function (Request $request) {
            $user = $request->user();
            $meetings = Meeting::with('host')
                ->where(function ($q) use ($user) {
                    $q->where('host_id', $user->id)
                        ->orWhereHas('attendanceSessions', fn($sub) => $sub->where('user_id', $user->id))
                        ->orWhere('status', 'ongoing');
                })
                ->latest('scheduled_at')
                ->paginate(20);

            return response()->json([
                'status' => 'success',
                'data' => $meetings,
            ]);
        });

        Route::post('/meetings', function (Request $request) {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'scheduled_at' => 'required|date',
                'duration_minutes' => 'nullable|integer',
                'password' => 'nullable|string',
                'lobby_enabled' => 'nullable|boolean',
                'allow_guests' => 'nullable|boolean',
                'start_muted' => 'nullable|boolean',
            ]);

            $meeting = app(\App\Services\MeetingService::class)->createScheduledMeeting(
                $request->user(),
                $validated
            );

            return response()->json([
                'status' => 'success',
                'meeting' => $meeting->load('host'),
            ], 201);
        });

        Route::post('/meetings/instant', function (Request $request) {
            $meeting = app(\App\Services\MeetingService::class)->createInstantMeeting(
                $request->user(),
                ['title' => $request->input('title')]
            );

            return response()->json([
                'status' => 'success',
                'meeting' => $meeting->load('host'),
            ], 201);
        });

        Route::get('/meetings/{code}', function (string $code) {
            $meeting = Meeting::with(['host', 'attendanceSummaries.user', 'actionItems', 'files', 'note'])
                ->where('room_code', $code)
                ->firstOrFail();

            return response()->json([
                'status' => 'success',
                'meeting' => $meeting,
            ]);
        });

        Route::post('/meetings/{code}/invitations', [MeetingInvitationController::class, 'invite']);

        // In-Meeting Notes & Files
        Route::get('/meetings/{code}/notes', [MeetingNoteController::class, 'getNotes']);
        Route::put('/meetings/{code}/notes', [MeetingNoteController::class, 'update']);
        Route::get('/meetings/{code}/files', [MeetingFileController::class, 'index']);
        Route::post('/meetings/{code}/files', [MeetingFileController::class, 'store']);

        // Notifications
        Route::get('/notifications', function (Request $request) {
            $notifications = Notification::where('user_id', $request->user()->id)
                ->latest()
                ->limit(30)
                ->get();

            return response()->json([
                'status' => 'success',
                'notifications' => $notifications,
            ]);
        });
    });
});

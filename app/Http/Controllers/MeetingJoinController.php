<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Services\AttendanceService;
use App\Services\JitsiJwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MeetingJoinController extends Controller
{
    protected JitsiJwtService $jwtService;
    protected AttendanceService $attendanceService;

    public function __construct(JitsiJwtService $jwtService, AttendanceService $attendanceService)
    {
        $this->jwtService = $jwtService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Show Pre-Join Lobby.
     */
    public function lobby(Request $request, string $code): View|RedirectResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();

        if ($meeting->status === 'cancelled') {
            return redirect()->route('dashboard')
                ->with('error', 'Rapat ini telah dibatalkan oleh penyelenggara.');
        }

        $user = Auth::user();

        if (!$user && !$meeting->allow_guests) {
            return redirect()->route('login')
                ->with('error', 'Rapat ini bersifat privat. Silakan login terlebih dahulu.');
        }

        return view('meetings.lobby', [
            'meeting' => $meeting,
            'user' => $user,
            'requiresPassword' => $meeting->hasPassword(),
        ]);
    }

    /**
     * Join the Conference Room (Web IFrame).
     */
    public function room(Request $request, string $code): View|RedirectResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();

        if ($meeting->status === 'cancelled') {
            return redirect()->route('dashboard')
                ->with('error', 'Rapat ini telah dibatalkan.');
        }

        $user = Auth::user();

        // Guest check
        if (!$user && !$meeting->allow_guests) {
            return redirect()->route('login')
                ->with('error', 'Rapat ini tidak mengizinkan tamu tanpa akun.');
        }

        // Guest name handling
        $guestName = $request->input('guest_name');
        if (!$user && empty($guestName)) {
            $guestName = $request->session()->get('guest_name_' . $meeting->room_code, 'Tamu ' . rand(100, 999));
        }

        // Password verification
        if ($meeting->hasPassword()) {
            $password = $request->input('password') ?? $request->session()->get('meeting_pwd_' . $meeting->room_code);
            if (!$password || !Hash::check($password, $meeting->password_hash)) {
                return redirect()->route('meetings.lobby', $code)
                    ->withErrors(['password' => 'Kata sandi ruang rapat tidak sesuai.'])
                    ->withInput();
            }
            $request->session()->put('meeting_pwd_' . $meeting->room_code, $password);
        }

        // Generate Jitsi JWT
        $jwt = $this->jwtService->generateToken($meeting, $user, $guestName);

        // Record attendance join session
        $platform = 'web';
        $session = $this->attendanceService->recordJoin(
            $meeting,
            $user,
            $guestName,
            $platform,
            $request->userAgent()
        );

        $displayName = $user ? $user->name : $guestName;
        $isModerator = $user && (($user->id === $meeting->host_id) || $user->isAdmin());

        return view('meetings.room', [
            'meeting' => $meeting,
            'jwt' => $jwt,
            'domain' => $this->jwtService->getDomain(),
            'isPublicDomain' => $this->jwtService->isPublicDomain(),
            'jitsiRoomName' => $meeting->jitsi_room_name,
            'sessionToken' => $session->session_token,
            'displayName' => $displayName,
            'isModerator' => $isModerator,
            'user' => $user,
            'startWithAudioMuted' => $meeting->start_muted || $request->boolean('start_muted_audio'),
            'startWithVideoMuted' => $request->boolean('start_muted_video'),
        ]);
    }

    /**
     * API Join endpoint for Mobile client (Flutter).
     */
    public function apiJoin(Request $request, string $code): JsonResponse
    {
        $meeting = Meeting::where('room_code', $code)->first();

        if (!$meeting) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rapat tidak ditemukan.',
            ], 404);
        }

        if ($meeting->status === 'cancelled') {
            return response()->json([
                'status' => 'error',
                'message' => 'Rapat telah dibatalkan.',
            ], 410);
        }

        $user = $request->user(); // via Sanctum
        $guestName = $request->input('guest_name');

        if (!$user && !$meeting->allow_guests) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rapat ini tidak mengizinkan tamu.',
            ], 403);
        }

        if ($meeting->hasPassword()) {
            $password = $request->input('password');
            if (!$password || !Hash::check($password, $meeting->password_hash)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kata sandi rapat diperlukan atau salah.',
                    'requires_password' => true,
                ], 401);
            }
        }

        $jwt = $this->jwtService->generateToken($meeting, $user, $guestName);
        $displayName = $user ? $user->name : ($guestName ?: 'Tamu');
        $isModerator = $user && (($user->id === $meeting->host_id) || $user->isAdmin());

        $session = $this->attendanceService->recordJoin(
            $meeting,
            $user,
            $guestName,
            $request->input('platform', 'mobile'),
            $request->userAgent()
        );

        return response()->json([
            'status' => 'success',
            'jitsi_domain' => $this->jwtService->getDomain(),
            'room_name' => $meeting->jitsi_room_name,
            'room_code' => $meeting->room_code,
            'meeting_title' => $meeting->title,
            'jwt' => $jwt,
            'role' => $isModerator ? 'moderator' : 'participant',
            'display_name' => $displayName,
            'session_token' => $session->session_token,
            'config' => [
                'startWithAudioMuted' => (bool) $meeting->start_muted,
                'startWithVideoMuted' => false,
            ],
        ]);
    }

    /**
     * Heartbeat endpoint for attendance accuracy.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');
        if (!$sessionToken) {
            return response()->json(['status' => 'error', 'message' => 'Missing session token'], 400);
        }

        $recorded = $this->attendanceService->recordHeartbeat($sessionToken);

        return response()->json(['status' => $recorded ? 'success' : 'not_found']);
    }

    /**
     * Leave meeting endpoint.
     */
    public function leave(Request $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');
        if (!$sessionToken) {
            return response()->json(['status' => 'error', 'message' => 'Missing session token'], 400);
        }

        $recorded = $this->attendanceService->recordLeave($sessionToken);

        return response()->json(['status' => $recorded ? 'success' : 'not_found']);
    }
}


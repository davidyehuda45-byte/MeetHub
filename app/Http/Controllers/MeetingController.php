<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Meeting;
use App\Services\MeetingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MeetingController extends Controller
{
    protected MeetingService $meetingService;

    public function __construct(MeetingService $meetingService)
    {
        $this->meetingService = $meetingService;
    }

    /**
     * Dashboard / Meeting list.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $ongoingMeetings = Meeting::with('host')
            ->where('status', 'ongoing')
            ->where(function ($q) use ($user) {
                $q->where('host_id', $user->id)
                    ->orWhereHas('attendanceSessions', fn($sub) => $sub->where('user_id', $user->id))
                    ->orWhere('allow_guests', true);
            })
            ->latest('started_at')
            ->get();

        $search = $request->input('search');

        $upcomingMeetings = Meeting::with('host')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now()->subHours(2))
            ->where(function ($q) use ($user) {
                $q->where('host_id', $user->id)
                    ->orWhereHas('invitations', fn($sub) => $sub->where('user_id', $user->id));
            })
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('title', 'like', "%{$s}%")
                        ->orWhere('room_code', 'like', "%{$s}%");
                });
            })
            ->orderBy('scheduled_at')
            ->paginate(10)
            ->withQueryString();

        $pastMeetings = Meeting::with('host')
            ->whereIn('status', ['ended', 'cancelled'])
            ->where(function ($q) use ($user) {
                $q->where('host_id', $user->id)
                    ->orWhereHas('attendanceSessions', fn($sub) => $sub->where('user_id', $user->id));
            })
            ->when($search, function ($q, $s) {
                $q->where('title', 'like', "%{$s}%");
            })
            ->latest('ended_at')
            ->limit(10)
            ->get();

        return view('meetings.index', compact('ongoingMeetings', 'upcomingMeetings', 'pastMeetings', 'search'));
    }

    /**
     * Show form to schedule a meeting.
     */
    public function create(): View
    {
        return view('meetings.create');
    }

    /**
     * Store a scheduled meeting.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required',
            'duration_minutes' => 'required|integer|min:5|max:1440',
            'password' => 'nullable|string|min:4',
            'lobby_enabled' => 'nullable|boolean',
            'allow_guests' => 'nullable|boolean',
            'start_muted' => 'nullable|boolean',
        ]);

        $scheduledAt = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']);

        $meeting = $this->meetingService->createScheduledMeeting(Auth::user(), [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => $validated['duration_minutes'],
            'password' => $validated['password'] ?? null,
            'lobby_enabled' => $request->boolean('lobby_enabled'),
            'allow_guests' => $request->has('allow_guests') ? $request->boolean('allow_guests') : true,
            'start_muted' => $request->boolean('start_muted'),
        ]);

        return redirect()->route('meetings.show', $meeting->room_code)
            ->with('success', 'Rapat berhasil dijadwalkan!');
    }

    /**
     * Instant meeting creation ("Mulai Sekarang").
     */
    public function storeInstant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
        ]);

        $meeting = $this->meetingService->createInstantMeeting(Auth::user(), [
            'title' => $validated['title'] ?? ('Rapat Cepat ' . Auth::user()->name),
            'allow_guests' => true,
        ]);

        return redirect()->route('meetings.room', $meeting->room_code);
    }

    /**
     * Detail view of meeting.
     */
    public function show(string $code): View
    {
        $meeting = Meeting::with([
            'host',
            'attendanceSummaries.user',
            'actionItems.assignee',
            'files.uploader',
            'note',
        ])->where('room_code', $code)->firstOrFail();

        $qrSvg = app(\App\Services\QrCodeService::class)->generateSvg($meeting->join_url, 160);

        return view('meetings.show', compact('meeting', 'qrSvg'));
    }

    /**
     * Show edit form.
     */
    public function edit(string $code): View
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();
        Gate::authorize('update', $meeting);

        return view('meetings.edit', compact('meeting'));
    }

    /**
     * Update meeting.
     */
    public function update(Request $request, string $code): RedirectResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();
        Gate::authorize('update', $meeting);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required',
            'duration_minutes' => 'required|integer|min:5',
            'lobby_enabled' => 'nullable|boolean',
            'allow_guests' => 'nullable|boolean',
            'start_muted' => 'nullable|boolean',
        ]);

        $meeting->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']),
            'duration_minutes' => $validated['duration_minutes'],
            'lobby_enabled' => $request->boolean('lobby_enabled'),
            'allow_guests' => $request->has('allow_guests') ? $request->boolean('allow_guests') : true,
            'start_muted' => $request->boolean('start_muted'),
        ]);

        return redirect()->route('meetings.show', $meeting->room_code)
            ->with('success', 'Pengaturan rapat berhasil diperbarui.');
    }

    /**
     * Delete or Cancel meeting.
     */
    public function destroy(string $code): RedirectResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();
        Gate::authorize('delete', $meeting);

        $this->meetingService->cancelMeeting($meeting, Auth::user());

        return redirect()->route('dashboard')
            ->with('success', 'Rapat telah dibatalkan.');
    }

    /**
     * End meeting by host.
     */
    public function end(string $code): RedirectResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();
        Gate::authorize('update', $meeting);

        $this->meetingService->endMeeting($meeting, Auth::user());

        return redirect()->route('meetings.show', $meeting->room_code)
            ->with('success', 'Rapat telah diakhiri.');
    }
}


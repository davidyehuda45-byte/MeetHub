<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Meeting;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Users management list.
     */
    public function users(Request $request): View
    {
        $search = $request->input('search');
        $users = User::query()
            ->when($search, function ($q, $s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.users', compact('users', 'search'));
    }

    /**
     * Update user role and status.
     */
    public function updateUser(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => 'required|in:admin,host,user',
            'is_active' => 'required|boolean',
        ]);

        $user->update($validated);

        AuditLog::log('admin_updated_user', User::class, $user->id, [
            'new_role' => $user->role,
            'is_active' => $user->is_active,
        ]);

        return back()->with('success', "Pengaturan user {$user->name} berhasil diperbarui.");
    }

    /**
     * Monitor all meetings.
     */
    public function meetings(Request $request): View
    {
        $status = $request->input('status');
        $meetings = Meeting::with('host')
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Meeting::count(),
            'ongoing' => Meeting::where('status', 'ongoing')->count(),
            'scheduled' => Meeting::where('status', 'scheduled')->count(),
            'ended' => Meeting::where('status', 'ended')->count(),
        ];

        return view('admin.meetings', compact('meetings', 'status', 'stats'));
    }

    /**
     * View audit logs.
     */
    public function auditLogs(): View
    {
        $logs = AuditLog::with('user')->latest()->paginate(25);
        return view('admin.audit_logs', compact('logs'));
    }

    /**
     * View system settings.
     */
    public function settings(): View
    {
        $settings = [
            'app_name' => Setting::get('app_name', config('app.name', 'MeetHub')),
            'jitsi_domain' => Setting::get('jitsi_domain', config('jitsi.domain', 'meet.jit.si')),
            'allow_guests_default' => (bool) Setting::get('allow_guests_default', true),
            'max_file_size_mb' => (int) Setting::get('max_file_size_mb', 20),
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Update system settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'jitsi_domain' => 'required|string|max:255',
            'allow_guests_default' => 'nullable|boolean',
            'max_file_size_mb' => 'required|integer|min:1|max:100',
        ]);

        Setting::set('app_name', $validated['app_name']);
        Setting::set('jitsi_domain', $validated['jitsi_domain']);
        Setting::set('allow_guests_default', $request->boolean('allow_guests_default') ? '1' : '0');
        Setting::set('max_file_size_mb', (string) $validated['max_file_size_mb']);

        AuditLog::log('admin_updated_settings', Setting::class, null, $validated);

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }
}


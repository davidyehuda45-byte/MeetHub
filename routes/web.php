<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ActionItemController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MeetingAttendanceController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingFileController;
use App\Http\Controllers\MeetingJoinController;
use App\Http\Controllers\MeetingNoteController;
use Illuminate\Support\Facades\Route;

// Guest / Public Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected User Area
Route::middleware('auth')->group(function () {
    Route::get('/', [MeetingController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [MeetingController::class, 'index']);

    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Meeting Management
    Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::post('/meetings/instant', [MeetingController::class, 'storeInstant'])->name('meetings.instant');
    Route::get('/meetings/{code}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::get('/meetings/{code}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
    Route::put('/meetings/{code}', [MeetingController::class, 'update'])->name('meetings.update');
    Route::delete('/meetings/{code}', [MeetingController::class, 'destroy'])->name('meetings.destroy');
    Route::post('/meetings/{code}/end', [MeetingController::class, 'end'])->name('meetings.end');
    Route::post('/meetings/{code}/invitations', [\App\Http\Controllers\MeetingInvitationController::class, 'invite'])->name('meetings.invitations.store');

    // Attendance Reports & Exports
    Route::get('/meetings/{code}/attendance/export', [MeetingAttendanceController::class, 'exportCsv'])->name('meetings.attendance.export');
    Route::get('/meetings/{code}/report', [MeetingAttendanceController::class, 'summaryReport'])->name('meetings.report');

    // Admin Panel
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::get('/meetings', [AdminController::class, 'meetings'])->name('meetings');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    });
});

// In-Meeting Collaborative Notes & Action Items
Route::get('/meetings/{code}/notes', [MeetingNoteController::class, 'getNotes'])->name('meetings.notes.get');
Route::put('/meetings/{code}/notes', [MeetingNoteController::class, 'update'])->name('meetings.notes.update');
Route::post('/meetings/{code}/action-items', [ActionItemController::class, 'store'])->name('meetings.action-items.store');
Route::post('/action-items/{id}/toggle', [ActionItemController::class, 'toggle'])->name('action-items.toggle');
Route::delete('/action-items/{id}', [ActionItemController::class, 'destroy'])->name('action-items.destroy');

// In-Meeting Shared Files
Route::get('/meetings/{code}/files', [MeetingFileController::class, 'index'])->name('meetings.files.index');
Route::post('/meetings/{code}/files', [MeetingFileController::class, 'store'])->name('meetings.files.store');
Route::get('/files/{id}', [MeetingFileController::class, 'download'])->name('files.download');
Route::delete('/files/{id}', [MeetingFileController::class, 'destroy'])->name('files.destroy');

// Pre-Join Lobby & Conference Room
Route::get('/m/{code}', [MeetingJoinController::class, 'lobby'])->name('meetings.lobby');
Route::match(['GET', 'POST'], '/m/{code}/room', [MeetingJoinController::class, 'room'])->name('meetings.room');
Route::post('/attendance/heartbeat', [MeetingJoinController::class, 'heartbeat'])->name('meetings.heartbeat');
Route::post('/attendance/leave', [MeetingJoinController::class, 'leave'])->name('meetings.leave');

<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Meeting;
use App\Models\MeetingRecording;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MeetingRecordingController extends Controller
{
    /**
     * Get all recordings for a meeting.
     */
    public function index(string $code): JsonResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();
        $this->authorizeAccess($meeting);

        $recordings = $meeting->recordings()
            ->latest()
            ->get()
            ->map(function ($rec) {
                return [
                    'id' => $rec->id,
                    'title' => $rec->title ?: $rec->file_name,
                    'file_name' => $rec->file_name,
                    'file_size' => $rec->formatted_size,
                    'duration' => $rec->formatted_duration,
                    'status' => $rec->status,
                    'download_url' => route('recordings.download', $rec->id),
                    'stream_url' => route('recordings.stream', $rec->id),
                    'created_at' => $rec->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'recordings' => $recordings,
        ]);
    }

    /**
     * Download recording file directly.
     */
    public function download(int $id): BinaryFileResponse|StreamedResponse
    {
        $recording = MeetingRecording::with('meeting')->findOrFail($id);
        $this->authorizeAccess($recording->meeting);

        $disk = Storage::disk($recording->storage_disk);

        if (!$disk->exists($recording->file_path)) {
            abort(404, 'Berkas rekaman tidak ditemukan pada server.');
        }

        AuditLog::log('recording_downloaded', MeetingRecording::class, $recording->id, [
            'meeting_id' => $recording->meeting_id,
            'file_name' => $recording->file_name,
        ]);

        return $disk->download($recording->file_path, $recording->file_name, [
            'Content-Type' => $recording->mime_type ?: 'video/mp4',
        ]);
    }

    /**
     * Stream video with HTTP Range header support (HTML5 video seekable).
     */
    public function stream(int $id, Request $request): StreamedResponse|BinaryFileResponse
    {
        $recording = MeetingRecording::with('meeting')->findOrFail($id);
        $this->authorizeAccess($recording->meeting);

        $disk = Storage::disk($recording->storage_disk);

        if (!$disk->exists($recording->file_path)) {
            abort(404, 'Berkas video tidak ditemukan.');
        }

        $fullPath = $disk->path($recording->file_path);

        if (file_exists($fullPath)) {
            return response()->file($fullPath, [
                'Content-Type' => $recording->mime_type ?: 'video/mp4',
                'Accept-Ranges' => 'bytes',
            ]);
        }

        // Fallback for cloud/virtual storage (S3 / MinIO)
        $stream = $disk->readStream($recording->file_path);
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $recording->mime_type ?: 'video/mp4',
            'Content-Length' => $recording->file_size,
            'Accept-Ranges' => 'bytes',
        ]);
    }

    /**
     * Delete a recording (Host or Admin only).
     */
    public function destroy(int $id): JsonResponse
    {
        $recording = MeetingRecording::with('meeting')->findOrFail($id);
        $user = Auth::user();

        if (!$user || ($user->id !== $recording->meeting->host_id && !$user->isAdmin())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak untuk menghapus rekaman ini.',
            ], 403);
        }

        Storage::disk($recording->storage_disk)->delete($recording->file_path);

        AuditLog::log('recording_deleted', MeetingRecording::class, $recording->id, [
            'meeting_id' => $recording->meeting_id,
            'file_name' => $recording->file_name,
        ]);

        $recording->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Rekaman berhasil dihapus.',
        ]);
    }

    /**
     * Helper to verify if the authenticated user has access to this meeting's recording.
     */
    protected function authorizeAccess(Meeting $meeting): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        if ($user->isAdmin() || $user->id === $meeting->host_id) {
            return;
        }

        // Check if user attended or was invited
        $isParticipant = $meeting->attendanceSummaries()->where('user_id', $user->id)->exists()
            || $meeting->invitations()->where('user_id', $user->id)->exists();

        if (!$isParticipant && !$meeting->allow_guests) {
            abort(403, 'Anda tidak terdaftar sebagai peserta dalam rapat ini.');
        }
    }
}


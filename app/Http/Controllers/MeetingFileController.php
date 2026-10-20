<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Meeting;
use App\Models\MeetingFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MeetingFileController extends Controller
{
    /**
     * List files for a meeting.
     */
    public function index(string $code): JsonResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();
        $files = MeetingFile::with('uploader')
            ->where('meeting_id', $meeting->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'files' => $files->map(function ($f) {
                return [
                    'id' => $f->id,
                    'name' => $f->original_name,
                    'size' => $f->formatted_size,
                    'mime' => $f->mime,
                    'uploader' => $f->uploader ? $f->uploader->name : 'Tamu',
                    'download_url' => route('files.download', $f->id),
                    'can_delete' => Auth::check() && (Auth::id() === $f->uploaded_by || Auth::id() === $f->meeting->host_id || Auth::user()->isAdmin()),
                ];
            }),
        ]);
    }

    /**
     * Upload file to a meeting.
     */
    public function store(Request $request, string $code): JsonResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:25600', // 25 MB max
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip,txt',
            ],
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $mime = $uploadedFile->getClientMimeType();
        $size = $uploadedFile->getSize();

        // Store file in storage/app/meeting_files/{meeting_id}
        $storedPath = $uploadedFile->store("meeting_files/{$meeting->id}");

        $meetingFile = MeetingFile::create([
            'meeting_id' => $meeting->id,
            'uploaded_by' => Auth::id(),
            'original_name' => $originalName,
            'stored_path' => $storedPath,
            'mime' => $mime,
            'size_bytes' => $size,
        ]);

        AuditLog::log('file_uploaded', MeetingFile::class, $meetingFile->id, [
            'filename' => $originalName,
            'size' => $size,
        ]);

        return response()->json([
            'status' => 'success',
            'file' => [
                'id' => $meetingFile->id,
                'name' => $meetingFile->original_name,
                'size' => $meetingFile->formatted_size,
                'mime' => $meetingFile->mime,
                'download_url' => route('files.download', $meetingFile->id),
            ],
        ]);
    }

    /**
     * Download meeting file securely.
     */
    public function download(int $id): BinaryFileResponse|JsonResponse
    {
        $file = MeetingFile::findOrFail($id);

        if (!Storage::exists($file->stored_path)) {
            return response()->json(['error' => 'File tidak ditemukan di server.'], 404);
        }

        return response()->download(
            Storage::path($file->stored_path),
            $file->original_name,
            ['Content-Type' => $file->mime]
        );
    }

    /**
     * Delete meeting file.
     */
    public function destroy(int $id): JsonResponse
    {
        $file = MeetingFile::with('meeting')->findOrFail($id);
        $user = Auth::user();

        $canDelete = $user && (
            $user->id === $file->uploaded_by ||
            $user->id === $file->meeting->host_id ||
            $user->isAdmin()
        );

        if (!$canDelete) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if (Storage::exists($file->stored_path)) {
            Storage::delete($file->stored_path);
        }

        $file->delete();

        AuditLog::log('file_deleted', MeetingFile::class, $id);

        return response()->json(['status' => 'success']);
    }
}


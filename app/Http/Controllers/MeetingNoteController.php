<?php

namespace App\Http\Controllers;

use App\Models\ActionItem;
use App\Models\AuditLog;
use App\Models\Meeting;
use App\Models\MeetingNote;
use App\Models\MeetingNoteVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingNoteController extends Controller
{
    /**
     * Get latest note content and action items.
     */
    public function getNotes(string $code): JsonResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();
        $note = MeetingNote::firstOrCreate(
            ['meeting_id' => $meeting->id],
            ['content' => '', 'updated_by' => Auth::id(), 'version' => 1]
        );

        $actionItems = ActionItem::with('assignee')
            ->where('meeting_id', $meeting->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'content' => $note->content ?? '',
            'version' => $note->version,
            'action_items' => $actionItems,
        ]);
    }

    /**
     * Autosave meeting notes.
     */
    public function update(Request $request, string $code): JsonResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();
        $user = Auth::user();

        // Note edit permission: host or admin
        $isHost = $user && ($user->id === $meeting->host_id || $user->isAdmin());
        if (!$isHost && !$meeting->allow_guests) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $content = $request->input('content', '');

        $note = MeetingNote::firstOrCreate(
            ['meeting_id' => $meeting->id],
            ['content' => '', 'updated_by' => $user?->id, 'version' => 1]
        );

        $note->content = $content;
        $note->updated_by = $user?->id;
        $note->version += 1;
        $note->save();

        // Record version snapshot if length changed significantly
        if ($note->version % 5 === 0) {
            MeetingNoteVersion::create([
                'note_id' => $note->id,
                'content' => $content,
                'created_by' => $user?->id,
                'created_at' => now(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'version' => $note->version,
            'saved_at' => now()->format('H:i:s'),
        ]);
    }
}


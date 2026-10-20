<?php

namespace App\Http\Controllers;

use App\Models\ActionItem;
use App\Models\Meeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionItemController extends Controller
{
    /**
     * Store new action item for a meeting.
     */
    public function store(Request $request, string $code): JsonResponse
    {
        $meeting = Meeting::where('room_code', $code)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $item = ActionItem::create([
            'meeting_id' => $meeting->id,
            'title' => $validated['title'],
            'assignee_id' => $validated['assignee_id'] ?? Auth::id(),
            'due_date' => $validated['due_date'] ?? null,
            'is_done' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'item' => $item->load('assignee'),
        ]);
    }

    /**
     * Toggle action item completion status.
     */
    public function toggle(int $id): JsonResponse
    {
        $item = ActionItem::findOrFail($id);
        $item->is_done = !$item->is_done;
        $item->save();

        return response()->json([
            'status' => 'success',
            'is_done' => $item->is_done,
        ]);
    }

    /**
     * Delete action item.
     */
    public function destroy(int $id): JsonResponse
    {
        $item = ActionItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }
}


<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\NoteLink;
use App\Models\Note;

class NoteLinkController extends Controller
{
    public function index(Request $request, string $note_id): JsonResponse
    {
        $note = Note::where('user_id', $request->user()->user_id)
            ->with(['goingLinks', 'backLinks'])
            ->findOrFail($note_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Data link catatan berhasil diambil',
            'data' => [
                'going_links' => $note->goingLinks,
                'back_links' => $note->backLinks,
            ],
        ], 200);
    }

    public function create(Request $request, string $note_id): JsonResponse
    {
        $request->validate([
            'target_note' => 'required|uuid',
        ]);

        $sourceNote = Note::where('user_id', $request->user()->user_id)
            ->findOrFail($note_id);

        $targetNote = Note::where('user_id', $request->user()->user_id)
            ->findOrFail($request->target_note);

        // Mencegah link ke diri sendiri
        if ($sourceNote->id === $targetNote->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak bisa menghubungkan catatan ke dirinya sendiri',
            ], 422);
        }

        $noteLink = NoteLink::firstOrCreate([
            'source_note' => $sourceNote->id,
            'target_note' => $targetNote->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data note_link berhasil dibuat',
            'data' => $noteLink,
        ], 200);
    }

    public function destroy(Request $request, string $id, string $target_id): JsonResponse
    {
        $noteLink = NoteLink::where('source_note', $id)
            ->where('target_note', $target_id)
            ->firstOrFail();

        $noteLink->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data note_link berhasil dihapus',
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Data\Note\NoteData;
use App\Data\NoteLink\NoteLinkData;
use App\Data\NoteLink\StoreNoteLinkData;
use App\Models\Note;
use App\Models\NoteLink;
use Illuminate\Http\JsonResponse;

class NoteLinkController extends Controller
{
    /**
     * Get all outgoing links and backlinks for a note.
     */
    public function index(Note $note): JsonResponse
    {
        $this->authorize('view', $note);

        return response()->json([
            'backlinks' => NoteData::collect($note->backlinks()->get()),
        ]);
    }

    /**
     * Create a link between two notes.
     */
    public function store(StoreNoteLinkData $data, Note $note): JsonResponse
    {
        $this->authorize('update', $note);

        $targetNote = Note::findOrFail($data->target_note);
        $this->authorize('view', $targetNote);

        $noteLink = NoteLink::firstOrCreate([
            'source_note' => $note->id,
            'target_note' => $data->target_note,
        ]);

        return response()->json(NoteLinkData::fromModel($noteLink), 201);
    }

    /**
     * Delete a link between two notes.
     */
    public function destroy(Note $note, Note $target): JsonResponse
    {
        $this->authorize('update', $note);

        $noteLink = NoteLink::where('source_note', $note->id)
            ->where('target_note', $target->id)
            ->firstOrFail();

        $noteLink->delete();

        return response()->json(null, 204);
    }
}

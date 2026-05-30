<?php

namespace App\Http\Controllers;

use App\Data\Note\NoteData;
use App\Data\Note\StoreNoteData;
use App\Data\Note\UpdateNoteData;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display a listing of the user's notes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Note::query()
            ->forUser($request->user())
            ->with(['tags']);

        // 2. Ambil filter dari Query Params (?folder_id=...)
        if ($request->query('folder_id')) {
            $query->where('folder_id', $request->query('folder_id'));
        }

        if ($request->query('search')) {
            $query->where('title', 'like', "%{$request->query('search')}%");
        }

        if ($request->query('tag_id')) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $request->query('tag_id')));
        }

        if ($request->boolean('trash')) {
            $query->onlyTrashed();
        }

        $notes = $query->orderByDesc('is_pinned')->orderByDesc('updated_at')->get();

        return response()->json(NoteData::collect($notes));
    }

    /**
     * Store a newly created note.
     */
    public function store(StoreNoteData $data, Request $request): JsonResponse
    {
        $note = $request->user()->notes()->create($data->toArray());

        return response()->json(NoteData::fromModel($note->load('tags')), 201);
    }

    /**
     * Display the specified note.
     */
    public function show(Request $request, Note $note): NoteData
    {
        $this->authorize('view', $note);

        return NoteData::fromModel($note->load(['tags', 'backlinks', 'outgoingLinks']));
    }

    /**
     * Update the specified note.
     */
    public function update(UpdateNoteData $data, Note $note): NoteData
    {
        $this->authorize('update', $note);

        $note->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return NoteData::fromModel($note->fresh()->load(['tags', 'backlinks']));
    }

    /**
     * Soft delete the specified note (move to trash).
     */
    public function destroy(Note $note): JsonResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return response()->json(null, 204);
    }

    /**
     * Restore a soft-deleted note from trash.
     */
    public function restore(Request $request, string $id): NoteData
    {
        $note = Note::withTrashed()
            ->forUser($request->user())
            ->findOrFail($id);

        $this->authorize('restore', $note);

        $note->restore();

        return NoteData::fromModel($note->fresh());
    }

    /**
     * Permanently delete the specified note.
     */
    public function forceDelete(Request $request, string $id): JsonResponse
    {
        $note = Note::withTrashed()
            ->forUser($request->user())
            ->findOrFail($id);

        $this->authorize('forceDelete', $note);

        $note->forceDelete();

        return response()->json(null, 204);
    }

    /**
     * Attach a tag to the note.
     */
    public function attachTag(Request $request, Note $note, string $tag): JsonResponse
    {
        $this->authorize('update', $note);

        $note->tags()->syncWithoutDetaching([$tag]);

        return response()->json(null, 204);
    }

    /**
     * Detach a tag from the note.
     */
    public function detachTag(Request $request, Note $note, string $tag): JsonResponse
    {
        $this->authorize('update', $note);

        $note->tags()->detach($tag);

        return response()->json(null, 204);
    }

    /**
     * Publish the note and generate a share token.
     */
    public function share(Note $note): NoteData
    {
        $this->authorize('update', $note);

        $note->generateShareToken();

        return NoteData::fromModel($note->fresh());
    }

    /**
     * Unpublish the note and revoke the share token.
     */
    public function unshare(Note $note): NoteData
    {
        $this->authorize('update', $note);

        $note->unpublish();

        return NoteData::fromModel($note->fresh());
    }
}

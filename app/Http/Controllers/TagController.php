<?php

namespace App\Http\Controllers;

use App\Data\Tag\StoreTagData;
use App\Data\Tag\TagData;
use App\Data\Tag\UpdateTagData;
use App\Models\Note;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the user's tags.
     */
    public function index(Request $request): JsonResponse
    {
        $tags = Tag::query()
            ->forUser($request->user())
            ->get();

        return response()->json(TagData::collect($tags));
    }

    /**
     * Store a newly created tag.
     */
    public function store(StoreTagData $data, Request $request): JsonResponse
    {
        $tag = $request->user()->tags()->create($data->toArray());

        return response()->json(TagData::fromModel($tag), 201);
    }

    /**
     * Update the specified tag.
     */
    public function update(UpdateTagData $data, Tag $tag): TagData
    {
        $this->authorize('update', $tag);

        $tag->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return TagData::fromModel($tag->fresh());
    }

    /**
     * Remove the specified tag.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return response()->json(null, 204);
    }

    /**
     * Attach a tag to a note.
     */
    public function attach(Request $request, Note $note, Tag $tag): JsonResponse
    {
        $this->authorize('update', $note);

        $note->tags()->syncWithoutDetaching([$tag->id]);

        return response()->json(null, 204);
    }

    /**
     * Detach a tag from a note.
     */
    public function detach(Request $request, Note $note, Tag $tag): JsonResponse
    {
        $this->authorize('update', $note);

        $note->tags()->detach($tag->id);

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers;

use App\Data\Note\NoteData;
use App\Models\Note;
use Illuminate\Http\JsonResponse;

class ShareController extends Controller
{
    /**
     * Display a publicly shared note by its token.
     */
    public function show(string $token): NoteData|JsonResponse
    {
        $note = Note::where('share_token', $token)
            ->where('is_published', true)
            ->first();

        if (! $note) {
            return response()->json(['message' => 'Note not found or not published.'], 404);
        }

        // Load relations for better public view
        return NoteData::fromModel($note->load(['tags', 'backlinks']));
    }
}

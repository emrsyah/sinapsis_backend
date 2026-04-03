<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Note;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::query();

        if ($request->folder_id) {
            $query->where('folder_id', $request->folder_id);
        }

        if ($request->title) {
            $query->where('title', 'like', "%{$request->title}%");
        }

        if ($request->active) {
            $query->active();
        } 

        if ($request->trash) {
            $query->trashed();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data note berhasil diambil',
            'data' => $query->get(),
        ], 200);
    }

    public function showOne(string $id)
    {
        $note = Note::findOrFail($id);

        if (!$note) {
            return response()->json(['message' => 'Catatan tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data note berhasil diambil',
            'data' => $note,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): JsonResponse
    {
        $note = Note::create([
            'user_id'   => $request->user()->user_id,
            'folder_id' => $request->folder_id ?: null,
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Note created.',
            'data'    => $note,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified data in storage.
     */
    public function update(Request $request, string $id)
    {
        $note = Note::findOrFail($id);

        if (!$note) {
            return response()->json(['message' => 'Catatan tidak ditemukan'], 404);
        }

        $note->update([
            'folder_id' => $request->folder_id,
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Note updated.',
            'data'    => $note,
        ], 200);
    }

    /**
     * Set deleted_at at the specified data from storage.
     */
    public function delete(string $id) 
    {
        $note = Note::findOrFail($id);
        $note->softDelete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil memindahkan catatan ke tong sampah',
            'data' => $note,
        ], 200);
    }

    /**
     * Remove the specified data from storage.
     */
    public function destroy(string $id)
    {
        $note = Note::findOrFail($id);
        
        $note->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan berhasil dihapus permanen',
            'data' => $note,
        ], 200);
    }

    public function restore(string $id)
    {
        $note = Note::findOrFail($id);

        $note->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengembalikan catatan dari tong sampah',
            'data' => $note,
        ], 200);
    }
}
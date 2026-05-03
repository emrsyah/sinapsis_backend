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
        $query = Note::query()->where('user_id', $request->user()->user_id);

        // 2. Ambil filter dari Query Params (?folder_id=...)
        if ($request->query('folder_id')) {
            $query->where('folder_id', $request->query('folder_id'));
        }

        if ($request->query('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->query('tag_id'));
            });
        }

        if ($request->query('search')) {
            $search = strtolower($request->query('search'));
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(content) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->query('trash') == 'true') {
            $query->trashed();
        } else {
            $query->active();
        }

        // 4. Ambil data beserta relasinya agar frontend tidak perlu fetch berkali-kali
        $notes = $query->with(['tags', 'folder'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data catatan berhasil diambil',
            'data' => $notes,
        ], 200);
    }


    public function showOne(Request $request, string $id)
    {
        $note = Note::where('user_id', $request->user()->user_id)
            ->where('id', $id)
            ->with(['goingLinks', 'backLinks', 'tags'])
            ->first();

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
     * Update the specified data in storage.
     */
    public function update(Request $request, string $id)
    {
        $note = Note::where('user_id', $request->user()->user_id)
            ->where('id', $id)
            ->firstOrFail();

        if (!$note) {
            return response()->json(['message' => 'Catatan tidak ditemukan'], 404);
        }

        $dataToUpdate = [];

        if ($request->has('folder_id')){
            $dataToUpdate['folder_id'] = $request['folder_id'];
        }

        if ($request->has('title')){
            $dataToUpdate['title'] = $request['title'];
        }

        if ($request->has('content')){
            $dataToUpdate['content'] = $request['content'];
        }

        if (!empty($dataToUpdate)){
            $note->update($dataToUpdate);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Note berhasil diupdate',
            'data' => $note,
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

    public function attachTag(Request $request, string $note_id, string $tag_id)
    {
        $note = Note::where('user_id', $request->user()->user_id)
            ->where('id', $note_id)
            ->firstOrFail();

        $note->tags()->attach($tag_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Tag berhasil ditambahkan ke note',
            'data' => $note,
        ], 200);
    }
}
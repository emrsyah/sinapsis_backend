<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = Tag::where('user_id', request()->user()->user_id)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data tag berhasil diambil',
            'data' => $tags,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:255',
        ]);

        $tag = Tag::create([
            'user_id' => $request->user()->user_id,
            'name' => $validated['name'],
            'color' => $validated['color'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tag berhasil dibuat',
            'data' => $tag,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tag = Tag::where('user_id', $request->user()->user_id)
            ->where('id', $id)
            ->firstOrFail();

        $dataToUpdate = [];

        if ($request->has('name')) {
            $dataToUpdate['name'] = $request->name;
        }

        if ($request->has('color')) {
            $dataToUpdate['color'] = $request->color;
        }

        if (!empty($dataToUpdate)) {
            $tag->update($dataToUpdate);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Tag berhasil diupdate',
            'data' => $tag,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tag = Tag::where('user_id', request()->user()->user_id)
            ->where('id', $id)
            ->firstOrFail();

        $tag->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Tag berhasil dihapus',
            'data' => $tag,
        ], 200);
    }

    public function attachToNote(Request $request, string $note_id, string $tag_id)
    {
        $tag = Tag::where('user_id', $request->user()->user_id)
            ->where('id', $tag_id)
            ->firstOrFail();

        $tag->notes()->attach($note_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Tag berhasil ditambahkan ke note',
            'data' => $tag,
        ], 200);
    }

    public function detachFromNote(Request $request, string $note_id, string $tag_id)
    {
        $tag = Tag::where('user_id', $request->user()->user_id)
            ->where('id', $tag_id)
            ->firstOrFail();

        $tag->notes()->detach($note_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Tag berhasil dihapus dari note',
            'data' => $tag,
        ], 200);
    }
}

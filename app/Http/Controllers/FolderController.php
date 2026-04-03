<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $folder = Folder::where('user_id', request()->user()->user_id)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data folder berhasil diambil',
            'data' => $folder,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $folder = Folder::create([
            'user_id' => $request->user()->user_id,
            'parent_id' => $request->parent_id ?? null,
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Folder berhasil dibuat',
            'data' => $folder,
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
     * Display the specified resource.
     */
    public function show(Folder $folder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Folder $folder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $folder = Folder::where('user_id', $request->user()->user_id)
            ->where('id', $id)
            ->firstOrFail();

        $dataToUpdate = [];

        if ($request->has('name')) {
            $dataToUpdate['name'] = $request->name;
        }

        if ($request->has('parent_id')) {
            $dataToUpdate['parent_id'] = $request->parent_id;
        }

        if (!empty($dataToUpdate)) {
            $folder->update($dataToUpdate);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Folder berhasil diupdate',
            'data' => $folder,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $folder = Folder::where('user_id', request()->user()->user_id)
            ->where('id', $id)
            ->firstOrFail();

        $folder->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Folder berhasil dihapus',
            'data' => $folder,
        ], 200);
    }
}

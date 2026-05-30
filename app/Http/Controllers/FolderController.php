<?php

namespace App\Http\Controllers;

use App\Data\Folder\FolderData;
use App\Data\Folder\StoreFolderData;
use App\Data\Folder\UpdateFolderData;
use App\Models\Folder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * Display nested tree of user's root folders.
     */
    public function index(Request $request): JsonResponse
    {
        $folders = Folder::query()
            ->forUser($request->user())
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        return response()->json(FolderData::collect($folders));
    }

    /**
     * Store a newly created folder.
     */
    public function store(StoreFolderData $data, Request $request): JsonResponse
    {
        $folder = $request->user()->folders()->create($data->toArray());

        return response()->json(FolderData::fromModel($folder), 201);
    }

    /**
     * Update the specified folder.
     */
    public function update(UpdateFolderData $data, Folder $folder): FolderData
    {
        $this->authorize('update', $folder);

        $folder->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return FolderData::fromModel($folder->fresh()->load('children'));
    }

    /**
     * Remove the specified folder.
     */
    public function destroy(Folder $folder): JsonResponse
    {
        $this->authorize('delete', $folder);

        $folder->delete();

        return response()->json(null, 204);
    }
}

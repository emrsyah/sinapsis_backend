<?php

namespace App\Http\Controllers;

use App\Data\Attachment\AttachmentData;
use App\Data\Attachment\StoreAttachmentData;
use App\Models\Attachment;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    /**
     * Display a listing of attachments for a specific note.
     */
    public function index(Note $note): JsonResponse
    {
        $this->authorize('view', $note);

        $attachments = $note->attachments()->latest()->get();

        return response()->json(AttachmentData::collect($attachments));
    }

    /**
     * Store a newly created attachment in storage.
     */
    public function store(StoreAttachmentData $data, Note $note, Request $request): JsonResponse
    {
        $this->authorize('update', $note);

        $file = $data->file;
        $user = $request->user();

        // Prepare path: attachments/{user_id}/{note_id}/{random_name}
        $path = "attachments/{$user->user_id}/{$note->id}/".Str::random(40).'.'.$file->getClientOriginalExtension();

        // Upload to Supabase (S3 compatible disk)
        $disk = Storage::disk('supabase');
        $disk->put($path, file_get_contents($file));

        // Get public URL (Supabase S3 infrastructure usually provides public links if bucket is public)
        // Since we don't have a custom URL generator in filesystems.php yet,
        // we'll construct it based on the Supabase structure or use the storage helper.
        $url = $disk->url($path);

        $attachment = Attachment::create([
            'note_id' => $note->id,
            'user_id' => $user->user_id,
            'file_url' => $url,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return response()->json(AttachmentData::fromModel($attachment), 201);
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        $this->authorize('delete', $attachment);

        // Get the base URL from the disk configuration
        $baseUrl = rtrim(config('filesystems.disks.supabase.url'), '/').'/';
        $path = str_replace($baseUrl, '', $attachment->file_url);

        Storage::disk('supabase')->delete($path);

        $attachment->delete();

        return response()->json(null, 204);
    }
}

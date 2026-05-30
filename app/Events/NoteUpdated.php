<?php

namespace App\Events;

use App\Models\Note;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoteUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Note $note) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("note.{$this->note->id}"),
            new PrivateChannel("App.Models.User.{$this->note->user_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'note.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->note->id,
            'user_id' => $this->note->user_id,
            'folder_id' => $this->note->folder_id,
            'title' => $this->note->title,
            'content' => $this->note->content,
            'is_published' => (bool) ($this->note->is_published ?? false),
            'is_pinned' => (bool) ($this->note->is_pinned ?? false),
            'share_token' => $this->note->share_token,
            'share_url' => $this->note->share_token
                ? url("/api/v1/shared/{$this->note->share_token}")
                : null,
            'deleted_at' => $this->note->deleted_at?->toISOString(),
            'created_at' => $this->note->created_at?->toISOString() ?? '',
            'updated_at' => $this->note->updated_at?->toISOString() ?? '',
        ];
    }
}

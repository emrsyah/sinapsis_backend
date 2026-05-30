<?php

namespace App\Data\Note;

use App\Data\Tag\TagData;
use App\Models\Note;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class NoteData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly ?string $folder_id,
        public readonly string $title,
        public readonly ?string $content,
        public readonly bool $is_published,
        public readonly bool $is_pinned,
        public readonly ?string $share_token,
        public readonly ?string $deleted_at,
        public readonly string $created_at,
        public readonly string $updated_at,
        /** @var TagData[] */
        #[DataCollectionOf(TagData::class)]
        public readonly ?DataCollection $tags = null,
        /** @var NoteData[] */
        #[DataCollectionOf(NoteData::class)]
        public readonly ?DataCollection $backlinks = null,
        /** @var NoteData[] */
        #[DataCollectionOf(NoteData::class)]
        public readonly ?DataCollection $outgoing_links = null,
        public readonly ?string $share_url = null,
    ) {}

    public static function fromModel(Note $note): self
    {
        return self::from([
            'id' => $note->id,
            'user_id' => $note->user_id,
            'folder_id' => $note->folder_id,
            'title' => $note->title,
            'content' => $note->content,
            'is_published' => (bool) ($note->is_published ?? false),
            'is_pinned' => (bool) ($note->is_pinned ?? false),
            'share_token' => $note->share_token,
            'deleted_at' => $note->deleted_at?->toISOString(),
            'created_at' => $note->created_at?->toISOString() ?? '',
            'updated_at' => $note->updated_at?->toISOString() ?? '',
            'tags' => $note->relationLoaded('tags')
                ? TagData::collect($note->tags)
                : null,
            'backlinks' => $note->relationLoaded('backlinks')
                ? NoteData::collect($note->backlinks)
                : null,
            'outgoing_links' => $note->relationLoaded('outgoingLinks')
                ? NoteData::collect($note->outgoingLinks)
                : null,
            'share_url' => $note->share_token
                ? url("/api/v1/shared/{$note->share_token}")
                : null,
        ]);
    }
}

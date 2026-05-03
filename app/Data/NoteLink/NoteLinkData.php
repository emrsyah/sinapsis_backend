<?php

namespace App\Data\NoteLink;

use App\Data\Note\NoteData;
use App\Models\NoteLink;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class NoteLinkData extends Data
{
    public function __construct(
        #[Required, Uuid]
        public readonly string $id,
        #[Required, Uuid]
        public readonly string $source_note_id,
        #[Required, Uuid]
        public readonly string $target_note_id,
        public readonly ?NoteData $source,
        public readonly ?NoteData $target,
        public readonly string $created_at,
    ) {}

    public static function fromModel(NoteLink $noteLink): self
    {
        return self::from([
            'id' => $noteLink->id,
            'source_note_id' => $noteLink->source_note,
            'target_note_id' => $noteLink->target_note,
            'source' => $noteLink->relationLoaded('source')
                ? NoteData::fromModel($noteLink->source)
                : null,
            'target' => $noteLink->relationLoaded('target')
                ? NoteData::fromModel($noteLink->target)
                : null,
            'created_at' => $noteLink->created_at?->toISOString() ?? '',
        ]);
    }
}

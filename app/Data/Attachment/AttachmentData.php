<?php

namespace App\Data\Attachment;

use App\Models\Attachment;
use Spatie\LaravelData\Data;

class AttachmentData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $note_id,
        public readonly string $file_url,
        public readonly string $file_name,
        public readonly ?string $file_type,
        public readonly ?int $file_size,
        public readonly string $created_at,
    ) {}

    public static function fromModel(Attachment $attachment): self
    {
        return new self(
            id: $attachment->id,
            note_id: $attachment->note_id,
            file_url: $attachment->file_url,
            file_name: $attachment->file_name,
            file_type: $attachment->file_type,
            file_size: $attachment->file_size,
            created_at: $attachment->created_at->toISOString(),
        );
    }
}

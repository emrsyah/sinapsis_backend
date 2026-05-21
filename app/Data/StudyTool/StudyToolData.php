<?php

namespace App\Data\StudyTool;

use Spatie\LaravelData\Data;

class StudyToolData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $note_id,
        public readonly string $type,
        public readonly array $content, // Otomatis menjadi array dari JSON
        public readonly ?string $image_url,
        public readonly string $status,
        public readonly string $created_at,
    ) {}

    public static function fromModel($studyTool): self
    {
        return new self(
            id: $studyTool->id,
            note_id: $studyTool->note_id,
            type: $studyTool->type,
            content: is_array($studyTool->content) ? $studyTool->content : json_decode($studyTool->content, true),
            image_url: $studyTool->image_url,
            status: $studyTool->status,
            created_at: $studyTool->created_at->toISOString(),
        );
    }
}

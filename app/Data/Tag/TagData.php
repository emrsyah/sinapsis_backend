<?php

namespace App\Data\Tag;

use App\Models\Tag;
use Spatie\LaravelData\Data;

class TagData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly string $name,
        public readonly ?string $color,
        public readonly ?string $created_at,
        public readonly int $notes_count,
    ) {}

    public static function fromModel(Tag $tag): self
    {
        return self::from([
            'id' => $tag->id,
            'user_id' => $tag->user_id,
            'name' => $tag->name,
            'color' => $tag->color,
            'created_at' => $tag->created_at?->toISOString(),
            'notes_count' => $tag->notes_count ?? 0,
        ]);
    }
}

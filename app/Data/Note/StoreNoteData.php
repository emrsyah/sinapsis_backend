<?php

namespace App\Data\Note;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class StoreNoteData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public readonly string $title,
        #[Nullable, StringType]
        public readonly ?string $content = null,
        #[Nullable, Uuid, Exists('folders', 'id')]
        public readonly ?string $folder_id = null,
        #[Required, BooleanType]
        public readonly bool $is_published = false,
    ) {}
}

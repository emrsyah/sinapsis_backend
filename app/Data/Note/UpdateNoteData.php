<?php

namespace App\Data\Note;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class UpdateNoteData extends Data
{
    public function __construct(
        #[StringType, Max(255)]
        public readonly ?string $title = null,
        #[Nullable, StringType]
        public readonly ?string $content = null,
        #[Nullable, Uuid, Exists('folders', 'id')]
        public readonly ?string $folder_id = null,
        public readonly ?bool $is_published = null,
    ) {}
}

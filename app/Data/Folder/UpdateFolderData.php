<?php

namespace App\Data\Folder;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class UpdateFolderData extends Data
{
    public function __construct(
        #[StringType, Max(255)]
        public readonly ?string $name = null,
        #[Nullable, Uuid]
        public readonly ?string $parent_id = null,
    ) {}
}

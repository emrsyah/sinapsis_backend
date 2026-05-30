<?php

namespace App\Data\User;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class UpdateUserData extends Data
{
    public function __construct(
        #[StringType, Max(255)]
        public readonly ?string $name = null,
        #[Nullable, StringType]
        public readonly ?string $image = null,
        #[Nullable, StringType]
        public readonly ?string $last_opened_note_id = null,
    ) {}
}

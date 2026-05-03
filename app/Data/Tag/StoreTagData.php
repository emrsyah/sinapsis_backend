<?php

namespace App\Data\Tag;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class StoreTagData extends Data
{
    public function __construct(
        #[Required, StringType, Max(100)]
        public readonly string $name,
        #[Nullable, StringType, Max(7)]
        public readonly ?string $color = null,
    ) {}
}

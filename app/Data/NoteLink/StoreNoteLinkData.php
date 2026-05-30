<?php

namespace App\Data\NoteLink;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class StoreNoteLinkData extends Data
{
    public function __construct(
        #[Required, Uuid]
        public readonly string $target_note,
    ) {}
}

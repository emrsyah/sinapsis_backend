<?php

namespace App\Data\StudyTool;

use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class StoreStudyToolData extends Data
{
    public function __construct(
        #[Required, Uuid]
        public readonly string $note_id,

        #[Required, In(['flashcard', 'quiz', 'mindmap'])]
        public readonly string $type,

        #[Required]
        public readonly array $content,

        public readonly ?string $image_url = null,

        #[Required, In(['pending', 'failed', 'completed'])]
        public readonly string $status = 'completed',
    ) {}
}

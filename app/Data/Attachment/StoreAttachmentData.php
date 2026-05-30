<?php

namespace App\Data\Attachment;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\Validation\File;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class StoreAttachmentData extends Data
{
    public function __construct(
        #[Required, File, Max(10240)] // Max 10MB
        public readonly UploadedFile $file,
    ) {}
}

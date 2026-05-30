<?php

namespace App\Data\Folder;

use App\Models\Folder;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class FolderData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly ?string $parent_id,
        public readonly string $name,
        public readonly string $created_at,
        public readonly string $updated_at,
        /** @var FolderData[] */
        #[DataCollectionOf(FolderData::class)]
        public readonly ?DataCollection $children = null,
    ) {}

    public static function fromModel(Folder $folder): self
    {
        return self::from([
            'id' => $folder->id,
            'user_id' => $folder->user_id,
            'parent_id' => $folder->parent_id,
            'name' => $folder->name,
            'created_at' => $folder->created_at?->toISOString() ?? '',
            'updated_at' => $folder->updated_at?->toISOString() ?? '',
            'children' => $folder->relationLoaded('children')
                ? FolderData::collect($folder->children)
                : null,
        ]);
    }
}

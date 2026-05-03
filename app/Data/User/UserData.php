<?php

namespace App\Data\User;

use App\Models\User;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public readonly string $user_id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $image,
        public readonly ?string $last_opened_note_id,
        public readonly string $created_at,
        public readonly string $updated_at,
    ) {}

    public static function fromModel(User $user): self
    {
        return self::from([
            'user_id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'image' => $user->image,
            'last_opened_note_id' => $user->last_opened_note_id,
            'created_at' => $user->created_at?->toISOString() ?? '',
            'updated_at' => $user->updated_at?->toISOString() ?? '',
        ]);
    }
}

<?php

namespace App\Policies;

use App\Models\NoteLink;
use App\Models\User;

class NoteLinkPolicy
{
    /**
     * Determine whether the user can view the note link.
     */
    public function view(User $user, NoteLink $noteLink): bool
    {
        return $user->user_id === $noteLink->source->user_id;
    }

    /**
     * Determine whether the user can delete the note link.
     */
    public function delete(User $user, NoteLink $noteLink): bool
    {
        return $user->user_id === $noteLink->source->user_id;
    }
}

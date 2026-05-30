<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    /**
     * Determine whether the user can view the attachment.
     */
    public function view(User $user, Attachment $attachment): bool
    {
        return $user->user_id === $attachment->user_id;
    }

    /**
     * Determine whether the user can delete the attachment.
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        return $user->user_id === $attachment->user_id;
    }
}

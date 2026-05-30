<?php

namespace App\Policies;

use App\Models\StudyTool;
use App\Models\User;

class StudyToolPolicy
{
    public function view(User $user, StudyTool $studyTool): bool
    {
        return $user->user_id === $studyTool->user_id;
    }

    public function delete(User $user, StudyTool $studyTool): bool
    {
        return $user->user_id === $studyTool->user_id;
    }
}

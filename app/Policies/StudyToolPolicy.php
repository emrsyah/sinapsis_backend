<?php

class StudyToolPolicy
{
    /**
     * Pastikan user adalah pemilik dari hasil generate ini.
     */
    public function view(User $user, StudyTool $studyTool): bool
    {
        return $user->user_id === $studyTool->user_id;
    }

    /**
     * User bisa menghapus hasil generate miliknya.
     */
    public function delete(User $user, StudyTool $studyTool): bool
    {
        return $user->user_id === $studyTool->user_id;
    }
}

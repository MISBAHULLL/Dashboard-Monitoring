<?php

namespace App\Policies;

use App\Models\TaskComment;
use App\Models\User;

class TaskCommentPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isMember();
    }

    public function delete(User $user, TaskComment $comment): bool
    {
        return $user->isAdmin() || $comment->user_id === $user->id;
    }

    public function pin(User $user, TaskComment $comment): bool
    {
        return $user->isAdmin();
    }
}

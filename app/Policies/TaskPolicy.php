<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

/**
 * Authorization policy for Task operations.
 *
 * Ensures users can only access and modify their own tasks.
 * Used by TaskController via $this->authorize() calls.
 */
class TaskPolicy
{
    /**
     * Determine if the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }

    /**
     * Determine if the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }
}

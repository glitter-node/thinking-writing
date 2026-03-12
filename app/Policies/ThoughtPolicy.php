<?php

namespace App\Policies;

use App\Domain\Thought\Models\Thought;
use App\Models\User;

class ThoughtPolicy
{
    public function view(User $user, Thought $thought): bool
    {
        return $thought->stream->space->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Thought $thought): bool
    {
        return $thought->stream->space->user_id === $user->id && $thought->user_id === $user->id;
    }

    public function delete(User $user, Thought $thought): bool
    {
        return $thought->stream->space->user_id === $user->id && $thought->user_id === $user->id;
    }
}

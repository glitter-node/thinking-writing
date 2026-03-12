<?php

namespace App\Policies;

use App\Domain\Space\Models\Space;
use App\Models\User;

class SpacePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Space $space): bool
    {
        return $space->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Space $space): bool
    {
        return $space->user_id === $user->id;
    }

    public function delete(User $user, Space $space): bool
    {
        return $space->user_id === $user->id;
    }
}

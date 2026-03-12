<?php

namespace App\Policies;

use App\Domain\Stream\Models\Stream;
use App\Models\User;

class StreamPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Stream $stream): bool
    {
        return $stream->space->user_id === $user->id;
    }

    public function delete(User $user, Stream $stream): bool
    {
        return $stream->space->user_id === $user->id;
    }
}

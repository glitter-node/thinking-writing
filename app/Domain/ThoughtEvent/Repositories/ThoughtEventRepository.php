<?php

namespace App\Domain\ThoughtEvent\Repositories;

use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtEvent\Models\ThoughtEvent;
use Illuminate\Database\Eloquent\Collection;

class ThoughtEventRepository
{
    public function create(Thought $thought, string $eventType, array $metadata = []): ThoughtEvent
    {
        return $thought->events()->create([
            'event_type' => $eventType,
            'metadata' => $metadata,
        ]);
    }

    public function forThought(Thought $thought): Collection
    {
        return $thought->events()->latest('id')->get();
    }
}

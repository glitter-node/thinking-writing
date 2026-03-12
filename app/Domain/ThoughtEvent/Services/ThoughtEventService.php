<?php

namespace App\Domain\ThoughtEvent\Services;

use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtEvent\Repositories\ThoughtEventRepository;
use Illuminate\Database\Eloquent\Collection;

class ThoughtEventService
{
    public function __construct(
        private readonly ThoughtEventRepository $thoughtEventRepository,
    ) {
    }

    public function recordEvent(Thought $thought, string $eventType, array $metadata = []): void
    {
        $this->thoughtEventRepository->create($thought, $eventType, $metadata);
    }

    public function getThoughtEvents(Thought $thought): Collection
    {
        return $this->thoughtEventRepository->forThought($thought);
    }
}

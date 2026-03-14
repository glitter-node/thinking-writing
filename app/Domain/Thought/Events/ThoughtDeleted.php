<?php

namespace App\Domain\Thought\Events;

use Carbon\CarbonImmutable;

final readonly class ThoughtDeleted extends AbstractThoughtLifecycleEvent
{
    public function __construct(
        int $thoughtId,
        int $spaceId,
        int $userId,
        int $streamId,
        public int $deletedPosition,
        ?CarbonImmutable $occurredAt = null,
    ) {
        parent::__construct($thoughtId, $spaceId, $userId, $streamId, $occurredAt);
    }
}

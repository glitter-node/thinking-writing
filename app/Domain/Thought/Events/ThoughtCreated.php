<?php

namespace App\Domain\Thought\Events;

use Carbon\CarbonImmutable;

final readonly class ThoughtCreated extends AbstractThoughtLifecycleEvent
{
    public function __construct(
        int $thoughtId,
        int $spaceId,
        int $userId,
        int $streamId,
        public string $source,
        ?CarbonImmutable $occurredAt = null,
    ) {
        parent::__construct($thoughtId, $spaceId, $userId, $streamId, $occurredAt);
    }
}

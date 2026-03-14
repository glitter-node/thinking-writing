<?php

namespace App\Domain\Thought\Events;

use Carbon\CarbonImmutable;

final readonly class ThoughtLinked extends AbstractThoughtLifecycleEvent
{
    /**
     * @param  array<int, int>  $linkedThoughtIds
     */
    public function __construct(
        int $thoughtId,
        int $spaceId,
        int $userId,
        int $streamId,
        public array $linkedThoughtIds,
        public string $source,
        ?CarbonImmutable $occurredAt = null,
    ) {
        parent::__construct($thoughtId, $spaceId, $userId, $streamId, $occurredAt);
    }
}

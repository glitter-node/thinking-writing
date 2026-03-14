<?php

namespace App\Domain\Thought\Events;

use Carbon\CarbonImmutable;

final readonly class ThoughtSynthesized extends AbstractThoughtLifecycleEvent
{
    /**
     * @param  array<int, int>  $sourceThoughtIds
     */
    public function __construct(
        int $thoughtId,
        int $spaceId,
        int $userId,
        int $streamId,
        public array $sourceThoughtIds,
        ?CarbonImmutable $occurredAt = null,
    ) {
        parent::__construct($thoughtId, $spaceId, $userId, $streamId, $occurredAt);
    }
}

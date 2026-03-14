<?php

namespace App\Domain\Thought\Events;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Events\Dispatchable;

abstract readonly class AbstractThoughtLifecycleEvent
{
    use Dispatchable;

    public CarbonImmutable $occurredAt;

    public function __construct(
        public int $thoughtId,
        public int $spaceId,
        public int $userId,
        public int $streamId,
        ?CarbonImmutable $occurredAt = null,
    ) {
        $this->occurredAt = $occurredAt ?? CarbonImmutable::now();
    }
}

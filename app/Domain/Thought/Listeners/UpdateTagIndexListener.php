<?php

namespace App\Domain\Thought\Listeners;

use App\Domain\Thought\Events\ThoughtCreated;
use App\Domain\Thought\Events\ThoughtDeleted;
use App\Domain\Thought\Events\ThoughtEvolved;
use App\Domain\Thought\Events\ThoughtLinked;
use App\Domain\Thought\Events\ThoughtPlaceholderCreated;
use App\Domain\Thought\Events\ThoughtSynthesized;
use App\Domain\Thought\Listeners\Concerns\ResolvesThought;
use App\Domain\ThoughtEmergence\Repositories\ThoughtTagIndexRepository;
use App\Domain\ThoughtEmergence\Services\ThoughtEmergenceService;

class UpdateTagIndexListener
{
    use ResolvesThought;

    public function __construct(
        private readonly ThoughtEmergenceService $thoughtEmergenceService,
        private readonly ThoughtTagIndexRepository $thoughtTagIndexRepository,
    ) {
    }

    public function handle(object $event): void
    {
        if ($event instanceof ThoughtDeleted) {
            $this->thoughtTagIndexRepository->deleteForThoughtId($event->thoughtId);

            return;
        }

        if ($event instanceof ThoughtLinked && $event->source !== 'update') {
            return;
        }

        if (! $event instanceof ThoughtCreated
            && ! $event instanceof ThoughtPlaceholderCreated
            && ! $event instanceof ThoughtEvolved
            && ! $event instanceof ThoughtSynthesized
            && ! $event instanceof ThoughtLinked) {
            return;
        }

        $thought = $this->resolveThought($event->thoughtId);
        $this->thoughtEmergenceService->updateThoughtIndexes($thought);
    }
}

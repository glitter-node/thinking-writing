<?php

namespace App\Domain\Thought\Listeners;

use App\Domain\Thought\Events\ThoughtCreated;
use App\Domain\Thought\Events\ThoughtEvolved;
use App\Domain\Thought\Events\ThoughtLinked;
use App\Domain\Thought\Events\ThoughtPlaceholderCreated;
use App\Domain\Thought\Events\ThoughtSynthesized;
use App\Domain\Thought\Listeners\Concerns\ResolvesThought;
use App\Domain\ThoughtVersion\Services\ThoughtVersionService;

class CreateThoughtVersionListener
{
    use ResolvesThought;

    public function __construct(
        private readonly ThoughtVersionService $thoughtVersionService,
    ) {
    }

    public function handle(object $event): void
    {
        if ($event instanceof ThoughtLinked) {
            if ($event->source !== 'update') {
                return;
            }

            $thought = $this->resolveThought($event->thoughtId);
            $this->thoughtVersionService->createInitialVersion($thought);
            $this->thoughtVersionService->createVersion($thought, $thought->content);

            return;
        }

        if (! $event instanceof ThoughtCreated
            && ! $event instanceof ThoughtPlaceholderCreated
            && ! $event instanceof ThoughtEvolved
            && ! $event instanceof ThoughtSynthesized) {
            return;
        }

        $thought = $this->resolveThought($event->thoughtId);
        $this->thoughtVersionService->createInitialVersion($thought);
    }
}

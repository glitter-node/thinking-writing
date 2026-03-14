<?php

namespace App\Domain\Thought\Listeners;

use App\Domain\Thought\Events\ThoughtCreated;
use App\Domain\Thought\Events\ThoughtDeleted;
use App\Domain\Thought\Events\ThoughtEvolved;
use App\Domain\Thought\Events\ThoughtLinked;
use App\Domain\Thought\Events\ThoughtPlaceholderCreated;
use App\Domain\Thought\Events\ThoughtSynthesized;
use App\Domain\ThoughtEmergence\Services\ThoughtEmergenceService;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateCooccurrenceListener implements ShouldQueue
{
    public function __construct(
        private readonly ThoughtEmergenceService $thoughtEmergenceService,
    ) {
    }

    public function handle(object $event): void
    {
        if ($event instanceof ThoughtLinked && $event->source !== 'update') {
            return;
        }

        if (! $event instanceof ThoughtCreated
            && ! $event instanceof ThoughtPlaceholderCreated
            && ! $event instanceof ThoughtEvolved
            && ! $event instanceof ThoughtSynthesized
            && ! $event instanceof ThoughtDeleted
            && ! $event instanceof ThoughtLinked) {
            return;
        }

        $this->thoughtEmergenceService->calculateCooccurrence($event->userId);
    }
}

<?php

namespace App\Domain\Thought\Listeners;

use App\Domain\Thought\Events\ThoughtCreated;
use App\Domain\Thought\Events\ThoughtEvolved;
use App\Domain\Thought\Events\ThoughtPlaceholderCreated;
use App\Domain\Thought\Events\ThoughtSynthesized;
use App\Domain\ThinkingSession\Services\ThinkingSessionService;

class RecordThinkingSessionListener
{
    public function __construct(
        private readonly ThinkingSessionService $thinkingSessionService,
    ) {
    }

    public function handle(object $event): void
    {
        if (! $event instanceof ThoughtCreated
            && ! $event instanceof ThoughtPlaceholderCreated
            && ! $event instanceof ThoughtEvolved
            && ! $event instanceof ThoughtSynthesized) {
            return;
        }

        $this->thinkingSessionService->recordThought($event->userId);
    }
}

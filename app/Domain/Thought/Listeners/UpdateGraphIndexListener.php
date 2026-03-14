<?php

namespace App\Domain\Thought\Listeners;

use App\Domain\Thought\Events\ThoughtDeleted;
use App\Domain\Thought\Events\ThoughtEvolved;
use App\Domain\Thought\Events\ThoughtLinked;
use App\Domain\Thought\Events\ThoughtSynthesized;
use App\Domain\Thought\Services\ThoughtGraphIndexService;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateGraphIndexListener implements ShouldQueue
{
    public function __construct(
        private readonly ThoughtGraphIndexService $thoughtGraphIndexService,
    ) {
    }

    public function handle(object $event): void
    {
        match (true) {
            $event instanceof ThoughtLinked => $this->thoughtGraphIndexService->updateGraphIndex($event->thoughtId),
            $event instanceof ThoughtEvolved => $this->refreshEvolution($event),
            $event instanceof ThoughtSynthesized => $this->refreshSynthesis($event),
            $event instanceof ThoughtDeleted => $this->thoughtGraphIndexService->refreshForDeletedThought($event->thoughtId),
            default => null,
        };
    }

    private function refreshEvolution(ThoughtEvolved $event): void
    {
        $this->thoughtGraphIndexService->updateGraphIndex($event->parentThoughtId);
        $this->thoughtGraphIndexService->updateGraphIndex($event->thoughtId);
    }

    private function refreshSynthesis(ThoughtSynthesized $event): void
    {
        $this->thoughtGraphIndexService->updateGraphIndex($event->thoughtId);

        foreach ($event->sourceThoughtIds as $sourceThoughtId) {
            $this->thoughtGraphIndexService->updateGraphIndex($sourceThoughtId);
        }
    }
}

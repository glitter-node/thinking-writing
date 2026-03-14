<?php

namespace App\Domain\Thought\Listeners;

use App\Domain\Thought\Events\ThoughtCreated;
use App\Domain\Thought\Events\ThoughtDeleted;
use App\Domain\Thought\Events\ThoughtEvolved;
use App\Domain\Thought\Events\ThoughtLinked;
use App\Domain\Thought\Events\ThoughtPlaceholderCreated;
use App\Domain\Thought\Events\ThoughtSynthesized;
use App\Domain\Thought\Listeners\Concerns\ResolvesThought;
use App\Domain\ThoughtEvent\Services\ThoughtEventService;

class RecordThoughtEventListener
{
    use ResolvesThought;

    public function __construct(
        private readonly ThoughtEventService $thoughtEventService,
    ) {
    }

    public function handle(object $event): void
    {
        match (true) {
            $event instanceof ThoughtCreated => $this->recordCreated($event),
            $event instanceof ThoughtPlaceholderCreated => $this->recordPlaceholder($event),
            $event instanceof ThoughtEvolved => $this->recordEvolved($event),
            $event instanceof ThoughtSynthesized => $this->recordSynthesized($event),
            $event instanceof ThoughtLinked => $this->recordLinked($event),
            $event instanceof ThoughtDeleted => $this->recordDeleted($event),
            default => null,
        };
    }

    private function recordCreated(ThoughtCreated $event): void
    {
        $thought = $this->resolveThought($event->thoughtId);

        $this->thoughtEventService->recordEvent($thought, 'ThoughtCreated', [
            'source' => $event->source,
        ]);
    }

    private function recordPlaceholder(ThoughtPlaceholderCreated $event): void
    {
        $thought = $this->resolveThought($event->thoughtId);

        $this->thoughtEventService->recordEvent($thought, 'ThoughtCreated', [
            'source' => 'placeholder',
        ]);
    }

    private function recordEvolved(ThoughtEvolved $event): void
    {
        $thought = $this->resolveThought($event->thoughtId);

        $this->thoughtEventService->recordEvent($thought, 'ThoughtCreated', [
            'source' => 'evolution',
            'parent_id' => $event->parentThoughtId,
        ]);
    }

    private function recordSynthesized(ThoughtSynthesized $event): void
    {
        $thought = $this->resolveThought($event->thoughtId);

        $this->thoughtEventService->recordEvent($thought, 'ThoughtCreated', [
            'source' => 'synthesis',
        ]);
        $this->thoughtEventService->recordEvent($thought, 'ThoughtSynthesized', [
            'source_thought_ids' => $event->sourceThoughtIds,
        ]);
    }

    private function recordLinked(ThoughtLinked $event): void
    {
        $thought = $this->resolveThought($event->thoughtId);

        if ($event->source === 'update') {
            $this->thoughtEventService->recordEvent($thought, 'ThoughtEdited', [
                'priority' => $thought->priority,
                'tags' => $thought->tags ?? [],
            ]);
        }

        $this->thoughtEventService->recordEvent($thought, 'ThoughtLinked', [
            'source' => $event->source,
        ]);
    }

    private function recordDeleted(ThoughtDeleted $event): void
    {
        $thought = $this->resolveThought($event->thoughtId, true);

        $this->thoughtEventService->recordEvent($thought, 'ThoughtArchived', [
            'stream_id' => $event->streamId,
        ]);
    }
}

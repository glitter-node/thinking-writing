<?php

namespace App\Modules\EmergenceModule;

use App\Core\Contracts\ThinkingModuleInterface;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtEmergence\Services\ThoughtEmergenceService;
use App\Events\ThoughtCreated;
use App\Events\ThoughtLinked;
use App\Events\ThoughtSynthesized;
use Illuminate\Support\Facades\Event;

class EmergenceModule implements ThinkingModuleInterface
{
    public function __construct(
        private readonly ThoughtEmergenceService $thoughtEmergenceService,
    ) {
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
        Event::listen(ThoughtCreated::class, fn (ThoughtCreated $event) => $this->processThought($event->thought, ['event' => 'thought_created'] + $event->context));
        Event::listen(ThoughtLinked::class, fn (ThoughtLinked $event) => $this->processThought($event->thought, ['event' => 'thought_linked'] + $event->context));
        Event::listen(ThoughtSynthesized::class, fn (ThoughtSynthesized $event) => $this->processThought($event->thought, ['event' => 'thought_synthesized'] + $event->context));
    }

    public function processThought(Thought $thought, array $context = []): void
    {
        $this->thoughtEmergenceService->updateThoughtIndexes($thought);
        $this->thoughtEmergenceService->calculateCooccurrence($thought->user_id);
    }
}

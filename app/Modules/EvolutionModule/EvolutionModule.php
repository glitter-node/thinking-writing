<?php

namespace App\Modules\EvolutionModule;

use App\Core\Contracts\ThinkingModuleInterface;
use App\Domain\Thought\Models\Thought;
use App\Events\ThoughtCreated;
use Illuminate\Support\Facades\Event;

class EvolutionModule implements ThinkingModuleInterface
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Event::listen(ThoughtCreated::class, function (ThoughtCreated $event): void {
            if ($event->thought->parent_id) {
                $this->processThought($event->thought, ['event' => 'evolved'] + $event->context);
            }
        });
    }

    public function processThought(Thought $thought, array $context = []): void
    {
    }
}

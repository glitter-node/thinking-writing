<?php

namespace App\Modules\SynthesisModule;

use App\Core\Contracts\ThinkingModuleInterface;
use App\Domain\Thought\Models\Thought;
use App\Events\ThoughtSynthesized;
use Illuminate\Support\Facades\Event;

class SynthesisModule implements ThinkingModuleInterface
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Event::listen(ThoughtSynthesized::class, function (ThoughtSynthesized $event): void {
            $this->processThought($event->thought, ['event' => 'synthesized'] + $event->context);
        });
    }

    public function processThought(Thought $thought, array $context = []): void
    {
    }
}

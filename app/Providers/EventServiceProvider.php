<?php

namespace App\Providers;

use App\Domain\Thought\Events\ThoughtCreated;
use App\Domain\Thought\Events\ThoughtDeleted;
use App\Domain\Thought\Events\ThoughtEvolved;
use App\Domain\Thought\Events\ThoughtLinked;
use App\Domain\Thought\Events\ThoughtPlaceholderCreated;
use App\Domain\Thought\Events\ThoughtSynthesized;
use App\Domain\Thought\Listeners\CreateThoughtVersionListener;
use App\Domain\Thought\Listeners\RecordThinkingSessionListener;
use App\Domain\Thought\Listeners\RecordThoughtEventListener;
use App\Domain\Thought\Listeners\UpdateCooccurrenceListener;
use App\Domain\Thought\Listeners\UpdateGraphIndexListener;
use App\Domain\Thought\Listeners\UpdateTagIndexListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ThoughtCreated::class => [
            CreateThoughtVersionListener::class,
            RecordThoughtEventListener::class,
            UpdateTagIndexListener::class,
            UpdateCooccurrenceListener::class,
            RecordThinkingSessionListener::class,
        ],
        ThoughtPlaceholderCreated::class => [
            CreateThoughtVersionListener::class,
            RecordThoughtEventListener::class,
            UpdateTagIndexListener::class,
            UpdateCooccurrenceListener::class,
            RecordThinkingSessionListener::class,
        ],
        ThoughtEvolved::class => [
            CreateThoughtVersionListener::class,
            RecordThoughtEventListener::class,
            UpdateTagIndexListener::class,
            UpdateCooccurrenceListener::class,
            UpdateGraphIndexListener::class,
            RecordThinkingSessionListener::class,
        ],
        ThoughtSynthesized::class => [
            CreateThoughtVersionListener::class,
            RecordThoughtEventListener::class,
            UpdateTagIndexListener::class,
            UpdateCooccurrenceListener::class,
            UpdateGraphIndexListener::class,
            RecordThinkingSessionListener::class,
        ],
        ThoughtLinked::class => [
            CreateThoughtVersionListener::class,
            RecordThoughtEventListener::class,
            UpdateTagIndexListener::class,
            UpdateCooccurrenceListener::class,
            UpdateGraphIndexListener::class,
        ],
        ThoughtDeleted::class => [
            RecordThoughtEventListener::class,
            UpdateTagIndexListener::class,
            UpdateCooccurrenceListener::class,
            UpdateGraphIndexListener::class,
        ],
    ];
}

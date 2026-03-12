<?php

namespace App\Modules\ReviewModule;

use App\Core\Contracts\ThinkingModuleInterface;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtReview\Services\ThoughtReviewService;
use App\Events\ThoughtReviewed;
use Illuminate\Support\Facades\Event;

class ReviewModule implements ThinkingModuleInterface
{
    public function __construct(
        private readonly ThoughtReviewService $thoughtReviewService,
    ) {
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
        Event::listen(ThoughtReviewed::class, function (ThoughtReviewed $event): void {
            $this->processThought($event->thought, ['event' => 'thought_reviewed'] + $event->context);
        });
    }

    public function processThought(Thought $thought, array $context = []): void
    {
        if (($context['action'] ?? $context['event'] ?? null) === 'review') {
            return;
        }

        $this->thoughtReviewService->getDailyReviewSet($thought->user_id, $thought->stream->space_id);
    }
}

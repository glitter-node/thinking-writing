<?php

namespace App\Domain\ThoughtReview\Repositories;

use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtReview\Models\ThoughtReview;
use Illuminate\Database\Eloquent\Collection;

class ThoughtReviewRepository
{
    public function createReview(Thought $thought, string $score): ThoughtReview
    {
        return $thought->reviews()->create([
            'reviewed_at' => now(),
            'review_score' => $score,
        ]);
    }

    public function getDailyReviewCandidates(int $userId, ?int $spaceId = null, int $limit = 5): Collection
    {
        return Thought::query()
            ->with(['stream:id,space_id,title'])
            ->withMax('reviews as last_reviewed_at', 'reviewed_at')
            ->where('thoughts.user_id', $userId)
            ->when($spaceId, fn ($query) => $query->whereHas('stream', fn ($streamQuery) => $streamQuery->where('space_id', $spaceId)))
            ->orderByRaw('CASE thoughts.priority WHEN "high" THEN 0 WHEN "medium" THEN 1 ELSE 2 END')
            ->orderByRaw('COALESCE(last_reviewed_at, "1970-01-01 00:00:00") asc')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}

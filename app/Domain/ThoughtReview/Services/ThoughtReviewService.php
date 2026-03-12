<?php

namespace App\Domain\ThoughtReview\Services;

use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtReview\Models\ThoughtReview;
use App\Domain\ThoughtReview\Repositories\ThoughtReviewRepository;
use Illuminate\Database\Eloquent\Collection;

class ThoughtReviewService
{
    public function __construct(private readonly ThoughtReviewRepository $thoughtReviewRepository)
    {
    }

    public function getDailyReviewSet(int $userId, ?int $spaceId = null): Collection
    {
        return $this->thoughtReviewRepository->getDailyReviewCandidates($userId, $spaceId);
    }

    public function recordReview(Thought $thought, string $score): ThoughtReview
    {
        return $this->thoughtReviewRepository->createReview($thought, $score);
    }
}

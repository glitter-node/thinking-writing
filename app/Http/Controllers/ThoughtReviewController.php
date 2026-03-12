<?php

namespace App\Http\Controllers;

use App\Core\Thought\ThoughtKernel;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtReview\Requests\StoreThoughtReviewRequest;
use Illuminate\Http\JsonResponse;

class ThoughtReviewController extends Controller
{
    public function __construct(private readonly ThoughtKernel $thoughtKernel)
    {
    }

    public function store(StoreThoughtReviewRequest $request, Thought $thought): JsonResponse
    {
        $review = $this->thoughtKernel->review($thought, $request->validated('review_score'));

        return response()->json([
            'message' => 'Review saved.',
            'review' => [
                'id' => $review->id,
                'thought_id' => $review->thought_id,
                'review_score' => $review->review_score,
            ],
        ], 201);
    }
}

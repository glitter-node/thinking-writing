<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtReview\Models\ThoughtReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThoughtReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_review_endpoint_returns_candidates_and_reviews_can_be_recorded(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thought = Thought::factory()->count(5)->for($user)->for($stream)->create();

        $response = $this->actingAs($user)->getJson(route('spaces.reviews', $space));

        $response->assertOk()->assertJsonCount(5, 'thoughts');

        $reviewResponse = $this->actingAs($user)
            ->postJson(route('thoughts.reviews.store', $thought->first()), [
                'review_score' => ThoughtReview::SCORE_USEFUL,
            ]);

        $reviewResponse
            ->assertCreated()
            ->assertJsonPath('review.review_score', ThoughtReview::SCORE_USEFUL);

        $this->assertDatabaseHas('thought_reviews', [
            'thought_id' => $thought->first()->id,
            'review_score' => ThoughtReview::SCORE_USEFUL,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtEmergence\Services\ThoughtEmergenceService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CooccurrenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cooccurrence_scoring_increases_for_shared_tags_and_synthesis(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thoughtA = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Reliability exploration',
            'tags' => ['systems', 'queues', 'latency'],
            'position' => 1,
        ]);
        $thoughtB = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Scaling exploration',
            'tags' => ['systems', 'queues', 'latency'],
            'position' => 2,
        ]);

        $this->actingAs($user)
            ->postJson(route('spaces.syntheses.store', $space), [
                'content' => 'Shared architecture direction',
                'thought_ids' => [$thoughtA->id, $thoughtB->id],
            ])
            ->assertCreated();

        app(ThoughtEmergenceService::class)->calculateCooccurrence($user->id);

        $this->assertDatabaseHas('thought_cooccurrence', [
            'thought_a_id' => min($thoughtA->id, $thoughtB->id),
            'thought_b_id' => max($thoughtA->id, $thoughtB->id),
            'score' => 11,
        ]);
    }
}

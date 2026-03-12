<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtEmergence\Services\ThoughtEmergenceService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuggestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggestions_endpoint_returns_related_thoughts_and_emerging_themes(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thoughtA = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Queue reliability',
            'tags' => ['systems', 'queues', 'latency'],
            'position' => 1,
        ]);
        $thoughtB = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Queue scaling',
            'tags' => ['systems', 'queues', 'latency'],
            'position' => 2,
        ]);

        app(ThoughtEmergenceService::class)->rebuildForUser($user->id);

        $this->actingAs($user)
            ->getJson(route('thoughts.suggestions', $thoughtA))
            ->assertOk()
            ->assertJsonPath('thought.id', $thoughtA->id)
            ->assertJsonFragment(['id' => $thoughtB->id])
            ->assertJsonFragment(['tag' => 'systems']);
    }
}

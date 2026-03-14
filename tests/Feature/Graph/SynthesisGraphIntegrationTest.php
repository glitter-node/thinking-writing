<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SynthesisGraphIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_synthesis_edges_appear_in_the_graph_links_api(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thoughtA = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Scaling idea',
            'position' => 1,
        ]);
        $thoughtB = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Resilience idea',
            'position' => 2,
        ]);

        $this->actingAs($user)
            ->postJson(route('spaces.syntheses.store', $space), [
                'content' => 'Combined architecture direction',
                'thought_ids' => [$thoughtA->id, $thoughtB->id],
            ])
            ->assertCreated();

        $synthesizedThought = Thought::query()->where('content', 'Combined architecture direction')->firstOrFail();

        $response = $this->actingAs($user)
            ->getJson(route('thoughts.links', $synthesizedThought));

        $response
            ->assertOk()
            ->assertJsonFragment([
                'source' => $thoughtA->id,
                'target' => $synthesizedThought->id,
                'type' => 'synthesis',
            ])
            ->assertJsonFragment([
                'source' => $thoughtB->id,
                'target' => $synthesizedThought->id,
                'type' => 'synthesis',
            ]);
    }
}

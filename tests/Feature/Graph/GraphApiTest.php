<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraphApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_graph_api_endpoint_returns_nodes_and_edges(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $source = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Idea A with [[Idea B]]',
            'position' => 1,
        ]);
        Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Idea B',
            'position' => 2,
        ]);

        $this->actingAs($user)
            ->patch(route('thoughts.update', $source), [
                'content' => $source->content,
                'priority' => 'medium',
                'tags' => '',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->actingAs($user)
            ->getJson(route('api.thoughts.graph', ['space' => $space->id]))
            ->assertOk()
            ->assertJsonStructure([
                'nodes' => [
                    '*' => ['data' => ['id', 'label']],
                ],
                'edges' => [
                    '*' => ['data' => ['source', 'target', 'type']],
                ],
            ])
            ->assertJsonFragment([
                'type' => 'link',
            ]);
    }

    public function test_links_endpoint_returns_connected_nodes_for_a_thought(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $source = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Source idea with [[Target idea]]',
            'position' => 1,
        ]);
        $target = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Target idea',
            'position' => 2,
        ]);

        $this->actingAs($user)
            ->patch(route('thoughts.update', $source), [
                'content' => $source->content,
                'priority' => 'medium',
                'tags' => '',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $response = $this->actingAs($user)
            ->getJson(route('thoughts.links', $source));

        $response
            ->assertOk()
            ->assertJsonPath('thought.id', $source->id)
            ->assertJsonCount(2, 'nodes')
            ->assertJsonFragment([
                'source' => $source->id,
                'target' => $target->id,
            ]);
    }
}

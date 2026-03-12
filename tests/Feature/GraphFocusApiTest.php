<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraphFocusApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_focus_api_returns_centered_graph_payload(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thoughtA = Thought::factory()->for($user)->for($stream)->create(['content' => 'Idea A', 'position' => 1]);
        Thought::factory()->for($user)->for($stream)->create(['content' => 'Idea B', 'position' => 2]);

        $this->actingAs($user)
            ->patch(route('thoughts.update', $thoughtA), [
                'content' => 'Idea A [[Idea B]]',
                'priority' => 'medium',
                'tags' => '',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->actingAs($user)
            ->getJson(route('api.thoughts.focus', $thoughtA, [
                'depth' => 1,
                'backlinks' => 1,
                'syntheses' => 1,
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'center' => ['id', 'label'],
                'nodes' => [
                    '*' => ['data' => ['id', 'label', 'isCenter']],
                ],
                'edges' => [
                    '*' => ['data' => ['source', 'target', 'type']],
                ],
            ])
            ->assertJsonPath('center.id', (string) $thoughtA->id);
    }
}

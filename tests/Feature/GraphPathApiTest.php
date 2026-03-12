<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraphPathApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_path_api_returns_shortest_connection_path(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thoughtA = Thought::factory()->for($user)->for($stream)->create(['content' => 'Thought A', 'position' => 1]);
        $thoughtB = Thought::factory()->for($user)->for($stream)->create(['content' => 'Thought B', 'position' => 2]);
        $thoughtC = Thought::factory()->for($user)->for($stream)->create(['content' => 'Thought C', 'position' => 3]);

        $this->actingAs($user)
            ->patch(route('thoughts.update', $thoughtA), [
                'content' => 'Thought A [[Thought B]]',
                'priority' => 'medium',
                'tags' => '',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->actingAs($user)
            ->patch(route('thoughts.update', $thoughtB), [
                'content' => 'Thought B [[Thought C]]',
                'priority' => 'medium',
                'tags' => '',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->actingAs($user)
            ->getJson(route('api.thoughts.path', [
                'from' => $thoughtA->id,
                'to' => $thoughtC->id,
            ]))
            ->assertOk()
            ->assertJsonPath('path.0', $thoughtA->id)
            ->assertJsonPath('path.1', $thoughtB->id)
            ->assertJsonPath('path.2', $thoughtC->id);
    }
}

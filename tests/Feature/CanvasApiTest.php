<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanvasApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_canvas_page_and_api_return_spatial_payload_for_a_space(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1, 'title' => 'Ideas']);
        $seed = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Seed thought',
            'position' => 1,
        ]);
        Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Evolved thought',
            'parent_id' => $seed->id,
            'position' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('canvas.index', ['space' => $space->id]))
            ->assertOk()
            ->assertSee('Spatial thinking canvas');

        $response = $this->actingAs($user)
            ->getJson(route('spaces.canvas', [
                'space' => $space,
                'x' => 0,
                'y' => 0,
                'width' => 1200,
                'height' => 900,
            ]));

        $response
            ->assertOk()
            ->assertJsonStructure([
                'viewport' => ['x', 'y', 'width', 'height'],
                'nodes',
                'edges',
                'clusters',
            ])
            ->assertJsonFragment([
                'type' => 'evolution',
            ]);
    }
}

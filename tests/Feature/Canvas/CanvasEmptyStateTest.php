<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanvasEmptyStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_canvas_empty_state_when_user_has_no_spaces(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('canvas.index'))
            ->assertOk()
            ->assertViewIs('empty.spaces')
            ->assertSee('No spaces yet')
            ->assertSee('Create your first space');
    }

    public function test_canvas_page_loads_normally_when_user_has_a_space(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        Stream::factory()->for($space)->create(['position' => 1]);

        $response = $this->actingAs($user)
            ->get(route('canvas.index', ['space' => $space->id]));

        $response
            ->assertOk()
            ->assertDontSee('No spaces yet')
            ->assertSee('Spatial thinking canvas');

        $this->assertNotSame('empty.spaces', $response->original->name());
    }
}

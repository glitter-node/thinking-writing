<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraphExplorerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_graph_explorer_page_loads_for_an_authenticated_space_owner(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        Stream::factory()->for($space)->create(['position' => 1]);

        $this->actingAs($user)
            ->get(route('graph.index', ['space' => $space->id]))
            ->assertOk()
            ->assertSee('Graph Explorer');
    }

    public function test_graph_explorer_renders_empty_state_when_user_has_no_spaces(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('graph.index'))
            ->assertOk()
            ->assertViewIs('empty.spaces')
            ->assertSee('No spaces yet')
            ->assertSee('Create your first space');
    }

    public function test_graph_path_renders_empty_state_when_user_has_no_spaces(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('graph.path'))
            ->assertOk()
            ->assertViewIs('empty.spaces')
            ->assertSee('No spaces yet')
            ->assertSee('Create your first space');
    }
}

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
}

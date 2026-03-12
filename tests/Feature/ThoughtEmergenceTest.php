<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtEmergence\Services\ThoughtEmergenceService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThoughtEmergenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_emergence_dashboard_and_board_panel_render_statistical_patterns(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);

        Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Distributed systems notes',
            'tags' => ['systems', 'latency', 'queues'],
            'position' => 1,
        ]);
        Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Queue retry notes',
            'tags' => ['systems', 'queues', 'retry'],
            'position' => 2,
        ]);

        app(ThoughtEmergenceService::class)->rebuildForUser($user->id);

        $this->actingAs($user)
            ->get(route('spaces.show', $space))
            ->assertOk()
            ->assertSee('Emerging ideas')
            ->assertSee('Possible connections');

        $this->actingAs($user)
            ->get(route('emergence.index', ['space' => $space->id]))
            ->assertOk()
            ->assertSee('Idea emergence')
            ->assertSee('Trending tags')
            ->assertSee('Strong co-occurrence');
    }
}

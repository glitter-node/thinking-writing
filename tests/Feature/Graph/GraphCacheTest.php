<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Services\ThoughtGraphIndexService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GraphCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_graph_neighbors_are_cached_and_invalidated_on_index_update(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thoughtA = Thought::factory()->for($user)->for($stream)->create(['content' => 'Cache A', 'position' => 1]);
        $thoughtB = Thought::factory()->for($user)->for($stream)->create(['content' => 'Cache B', 'position' => 2]);

        $this->actingAs($user)
            ->patch(route('thoughts.update', $thoughtA), [
                'content' => 'Cache A [[Cache B]]',
                'priority' => 'medium',
                'tags' => '',
            ])
            ->assertRedirect(route('spaces.show', $space));

        /** @var ThoughtGraphIndexService $service */
        $service = app(ThoughtGraphIndexService::class);
        $service->getConnectedThoughts($thoughtA->id, 1);

        $cacheKey = "thought_graph:{$thoughtA->id}:1:fallback";
        $this->assertTrue(Cache::has($cacheKey));

        $service->updateGraphIndex($thoughtA->id);

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_graph_reads_do_not_rebuild_missing_index_rows(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thought = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Read only graph node',
            'position' => 1,
        ]);

        $this->assertDatabaseCount('thought_graph_index', 0);

        $this->actingAs($user)
            ->getJson(route('thoughts.graph', $thought).'?depth=2')
            ->assertOk()
            ->assertJsonPath('thought.id', $thought->id)
            ->assertJsonCount(1, 'nodes')
            ->assertJsonCount(0, 'edges');

        $this->assertDatabaseCount('thought_graph_index', 0);
    }
}

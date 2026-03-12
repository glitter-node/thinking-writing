<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Services\ThoughtGraphIndexService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraphIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_graph_index_rebuilds_direct_evolution_and_synthesis_edges(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $baseThought = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Base idea',
            'position' => 1,
        ]);
        $linkedThought = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Linked idea',
            'position' => 2,
        ]);

        $this->actingAs($user)
            ->patch(route('thoughts.update', $baseThought), [
                'content' => 'Base idea with [[Linked idea]]',
                'priority' => 'medium',
                'tags' => '',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->actingAs($user)
            ->postJson(route('thoughts.evolve', $baseThought), [
                'content' => 'Evolved idea',
                'priority' => 'high',
                'tags' => '',
            ])
            ->assertCreated();

        $evolvedThought = Thought::query()->where('parent_id', $baseThought->id)->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('spaces.syntheses.store', $space), [
                'content' => 'Synthesized outcome',
                'thought_ids' => [$baseThought->id, $linkedThought->id],
            ])
            ->assertCreated();

        $synthesizedThought = Thought::query()->where('content', 'Synthesized outcome')->firstOrFail();

        app(ThoughtGraphIndexService::class)->rebuildGraphIndex();

        $this->assertDatabaseHas('thought_graph_index', [
            'thought_id' => $baseThought->id,
            'linked_thought_id' => $linkedThought->id,
            'link_type' => 'direct',
            'depth' => 1,
        ]);
        $this->assertDatabaseHas('thought_graph_index', [
            'thought_id' => $baseThought->id,
            'linked_thought_id' => $evolvedThought->id,
            'link_type' => 'evolution',
            'depth' => 1,
        ]);
        $this->assertDatabaseHas('thought_graph_index', [
            'thought_id' => $baseThought->id,
            'linked_thought_id' => $synthesizedThought->id,
            'link_type' => 'synthesis',
            'depth' => 1,
        ]);
    }
}

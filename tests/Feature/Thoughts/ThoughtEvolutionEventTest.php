<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Events\ThoughtEvolved as DomainThoughtEvolved;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ThoughtEvolutionEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_thought_evolution_dispatches_domain_event_and_persists_side_effects(): void
    {
        $evolvedEvent = null;

        Event::listen(DomainThoughtEvolved::class, function (DomainThoughtEvolved $event) use (&$evolvedEvent): void {
            $evolvedEvent = $event;
        });

        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thought = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Base idea',
            'position' => 1,
        ]);

        $this->actingAs($user)
            ->postJson(route('thoughts.evolve', $thought), [
                'content' => 'Evolved idea',
                'priority' => 'high',
                'tags' => 'domain, events',
            ])
            ->assertCreated();

        $evolvedThought = Thought::query()->where('parent_id', $thought->id)->firstOrFail();

        $this->assertNotNull($evolvedEvent);
        $this->assertSame($evolvedThought->id, $evolvedEvent->thoughtId);
        $this->assertSame($thought->id, $evolvedEvent->parentThoughtId);

        $this->assertDatabaseHas('thought_versions', [
            'thought_id' => $evolvedThought->id,
            'version' => 1,
            'content' => 'Evolved idea',
        ]);
        $this->assertDatabaseHas('thought_events', [
            'thought_id' => $evolvedThought->id,
            'event_type' => 'ThoughtCreated',
        ]);
        $this->assertDatabaseHas('thought_graph_index', [
            'thought_id' => $evolvedThought->id,
            'linked_thought_id' => $thought->id,
            'link_type' => 'evolution',
            'depth' => 1,
        ]);
        $this->assertDatabaseHas('thought_tag_index', [
            'thought_id' => $evolvedThought->id,
            'tag' => 'domain',
        ]);
    }
}

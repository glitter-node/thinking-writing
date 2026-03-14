<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Events\ThoughtCreated as DomainThoughtCreated;
use App\Domain\Thought\Events\ThoughtLinked as DomainThoughtLinked;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ThoughtCreationEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_thought_creation_dispatches_domain_events_and_persists_lifecycle_side_effects(): void
    {
        $createdEvent = null;
        $linkedEvent = null;

        Event::listen(DomainThoughtCreated::class, function (DomainThoughtCreated $event) use (&$createdEvent): void {
            $createdEvent = $event;
        });
        Event::listen(DomainThoughtLinked::class, function (DomainThoughtLinked $event) use (&$linkedEvent): void {
            $linkedEvent = $event;
        });

        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $existingThought = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Existing concept',
            'position' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('streams.thoughts.store', $stream), [
                'content' => 'New note about [[Existing concept]]',
                'priority' => 'high',
                'tags' => 'events, graph',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $thought = Thought::query()->where('content', 'New note about [[Existing concept]]')->firstOrFail();

        $this->assertNotNull($createdEvent);
        $this->assertSame($thought->id, $createdEvent->thoughtId);
        $this->assertSame('standard', $createdEvent->source);
        $this->assertNotNull($linkedEvent);
        $this->assertSame($thought->id, $linkedEvent->thoughtId);
        $this->assertSame([$existingThought->id], $linkedEvent->linkedThoughtIds);

        $this->assertDatabaseHas('thought_versions', [
            'thought_id' => $thought->id,
            'version' => 1,
            'content' => 'New note about [[Existing concept]]',
        ]);
        $this->assertDatabaseHas('thought_events', [
            'thought_id' => $thought->id,
            'event_type' => 'ThoughtCreated',
        ]);
        $this->assertDatabaseHas('thought_events', [
            'thought_id' => $thought->id,
            'event_type' => 'ThoughtLinked',
        ]);
        $this->assertDatabaseHas('thought_tag_index', [
            'thought_id' => $thought->id,
            'tag' => 'events',
        ]);
        $this->assertDatabaseHas('thought_graph_index', [
            'thought_id' => $thought->id,
            'linked_thought_id' => $existingThought->id,
            'link_type' => 'direct',
            'depth' => 1,
        ]);
        $this->assertDatabaseHas('thinking_sessions', [
            'user_id' => $user->id,
            'thought_count' => 1,
        ]);
    }
}

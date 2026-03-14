<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Events\ThoughtSynthesized as DomainThoughtSynthesized;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ThoughtSynthesisEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_thought_synthesis_dispatches_domain_event_and_persists_side_effects(): void
    {
        $synthesizedEvent = null;

        Event::listen(DomainThoughtSynthesized::class, function (DomainThoughtSynthesized $event) use (&$synthesizedEvent): void {
            $synthesizedEvent = $event;
        });

        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $thoughtA = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Idea A',
            'position' => 1,
            'tags' => ['events'],
        ]);
        $thoughtB = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Idea B',
            'position' => 2,
            'tags' => ['events', 'testing'],
        ]);

        $this->actingAs($user)
            ->postJson(route('spaces.syntheses.store', $space), [
                'content' => 'Merged idea',
                'thought_ids' => [$thoughtA->id, $thoughtB->id],
            ])
            ->assertCreated();

        $synthesizedThought = Thought::query()->where('content', 'Merged idea')->firstOrFail();

        $this->assertNotNull($synthesizedEvent);
        $this->assertSame($synthesizedThought->id, $synthesizedEvent->thoughtId);
        $this->assertSame([$thoughtA->id, $thoughtB->id], $synthesizedEvent->sourceThoughtIds);

        $this->assertDatabaseHas('thought_versions', [
            'thought_id' => $synthesizedThought->id,
            'version' => 1,
            'content' => 'Merged idea',
        ]);
        $this->assertDatabaseHas('thought_events', [
            'thought_id' => $synthesizedThought->id,
            'event_type' => 'ThoughtCreated',
        ]);
        $this->assertDatabaseHas('thought_events', [
            'thought_id' => $synthesizedThought->id,
            'event_type' => 'ThoughtSynthesized',
        ]);
        $this->assertDatabaseHas('thought_graph_index', [
            'thought_id' => $synthesizedThought->id,
            'linked_thought_id' => $thoughtA->id,
            'link_type' => 'synthesis',
            'depth' => 1,
        ]);
        $this->assertDatabaseHas('thought_tag_index', [
            'thought_id' => $synthesizedThought->id,
            'tag' => 'testing',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThoughtLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_thought_with_inline_links_creates_edges_and_placeholders(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $existingThought = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Distributed systems concept',
            'position' => 1,
        ]);

        $response = $this->actingAs($user)
            ->post(route('streams.thoughts.store', $stream), [
                'content' => 'This idea relates to [[Distributed systems concept]] and [[New placeholder idea]].',
                'priority' => 'high',
                'tags' => 'graph',
            ]);

        $response->assertRedirect(route('spaces.show', $space));

        $sourceThought = Thought::query()
            ->where('content', 'This idea relates to [[Distributed systems concept]] and [[New placeholder idea]].')
            ->firstOrFail();

        $placeholderThought = Thought::query()
            ->where('content', 'New placeholder idea')
            ->firstOrFail();

        $this->assertDatabaseHas('thought_links', [
            'source_thought_id' => $sourceThought->id,
            'target_thought_id' => $existingThought->id,
        ]);

        $this->assertDatabaseHas('thought_links', [
            'source_thought_id' => $sourceThought->id,
            'target_thought_id' => $placeholderThought->id,
        ]);
    }

    public function test_placeholder_thoughts_created_from_inline_links_use_the_normal_lifecycle(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);

        $this->actingAs($user)
            ->post(route('streams.thoughts.store', $stream), [
                'content' => 'Capture [[NewLink]] now.',
                'priority' => 'high',
                'tags' => 'graph',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $sourceThought = Thought::query()
            ->where('content', 'Capture [[NewLink]] now.')
            ->firstOrFail();

        $placeholderThought = Thought::query()
            ->where('content', 'NewLink')
            ->firstOrFail();

        $this->assertDatabaseHas('thought_versions', [
            'thought_id' => $placeholderThought->id,
            'version' => 1,
            'content' => 'NewLink',
        ]);
        $this->assertDatabaseHas('thought_events', [
            'thought_id' => $placeholderThought->id,
            'event_type' => 'ThoughtCreated',
        ]);
        $this->assertDatabaseHas('thought_graph_index', [
            'thought_id' => $sourceThought->id,
            'linked_thought_id' => $placeholderThought->id,
            'link_type' => 'direct',
            'depth' => 1,
        ]);
    }
}

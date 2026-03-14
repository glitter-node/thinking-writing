<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BacklinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_space_board_renders_backlinks_for_referenced_thoughts(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        $targetThought = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'Distributed systems concept',
            'position' => 1,
        ]);

        $sourceThought = Thought::factory()->for($user)->for($stream)->create([
            'content' => 'This note links [[Distributed systems concept]]',
            'position' => 2,
        ]);

        $this->actingAs($user)
            ->patch(route('thoughts.update', $sourceThought), [
                'content' => $sourceThought->content,
                'priority' => 'medium',
                'tags' => '',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->actingAs($user)
            ->get(route('spaces.show', $space))
            ->assertOk()
            ->assertSee('Referenced By')
            ->assertSee('This note links [[Distributed systems concept]]', false)
            ->assertSee('Linked Thoughts');

        $this->assertDatabaseHas('thought_links', [
            'source_thought_id' => $sourceThought->id,
            'target_thought_id' => $targetThought->id,
        ]);
    }
}

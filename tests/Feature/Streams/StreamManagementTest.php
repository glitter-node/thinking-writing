<?php

namespace Tests\Feature\Streams;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_update_and_delete_a_stream(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create([
            'title' => 'Inbox',
            'position' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('spaces.streams.store', $space), [
                'title' => 'Research',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $created = Stream::query()
            ->where('space_id', $space->id)
            ->where('title', 'Research')
            ->firstOrFail();

        $this->actingAs($user)
            ->patch(route('streams.update', $stream), [
                'title' => 'Inbox Updated',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('streams', [
            'id' => $stream->id,
            'title' => 'Inbox Updated',
        ]);

        $this->actingAs($user)
            ->delete(route('streams.destroy', $created))
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseMissing('streams', [
            'id' => $created->id,
        ]);
    }
}

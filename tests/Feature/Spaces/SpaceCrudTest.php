<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_update_and_delete_a_space(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('spaces.store'), [
                'title' => 'Ideas',
                'description' => 'Personal board',
            ])
            ->assertRedirect();

        $space = Space::query()->firstOrFail();

        $this->assertDatabaseHas('spaces', [
            'id' => $space->id,
            'title' => 'Ideas',
        ]);
        $this->assertCount(3, $space->streams);

        $this->actingAs($user)
            ->patch(route('spaces.update', $space), [
                'title' => 'Refined Ideas',
                'description' => 'Updated',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('spaces', [
            'id' => $space->id,
            'title' => 'Refined Ideas',
        ]);

        $this->actingAs($user)
            ->delete(route('spaces.destroy', $space))
            ->assertRedirect(route('spaces.index'));

        $this->assertDatabaseMissing('spaces', [
            'id' => $space->id,
        ]);
    }
}

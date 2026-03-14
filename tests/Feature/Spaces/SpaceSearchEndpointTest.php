<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaceSearchEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_endpoint_returns_matching_thoughts_for_a_space(): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        $stream = Stream::factory()->for($space)->create(['position' => 1]);
        Thought::factory()->for($user)->for($stream)->create(['content' => 'Build the instant search flow']);
        Thought::factory()->for($user)->for($stream)->create(['content' => 'Refine the onboarding copy']);

        $response = $this->actingAs($user)
            ->getJson(route('spaces.search', $space).'?q=instant');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'thoughts')
            ->assertJsonPath('thoughts.0.stream_id', $stream->id);

        $this->assertStringContainsString('<mark', $response->json('thoughts.0.highlighted_content'));
    }
}

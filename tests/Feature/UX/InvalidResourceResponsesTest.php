<?php

namespace Tests\Feature\UX;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvalidResourceResponsesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidRouteProvider(): array
    {
        return [
            'missing space page' => ['/spaces/999999'],
            'missing space canvas' => ['/spaces/999999/canvas'],
            'missing graph focus' => ['/graph/999999'],
            'missing thought graph' => ['/thoughts/999999/graph'],
        ];
    }

    /**
     * @dataProvider invalidRouteProvider
     */
    public function test_invalid_resources_return_not_found(string $route): void
    {
        $user = $this->createUserWithWorkspace();

        $this->actingAs($user)
            ->get($route)
            ->assertNotFound();
    }

    public function test_graph_path_api_rejects_cross_space_requests(): void
    {
        $user = User::factory()->create();

        $firstSpace = Space::factory()->for($user)->create();
        $firstStream = Stream::factory()->for($firstSpace)->create(['position' => 1]);
        $firstThought = Thought::factory()->for($user)->for($firstStream)->create(['position' => 1]);

        $secondSpace = Space::factory()->for($user)->create();
        $secondStream = Stream::factory()->for($secondSpace)->create(['position' => 1]);
        $secondThought = Thought::factory()->for($user)->for($secondStream)->create(['position' => 1]);

        $this->actingAs($user)
            ->getJson(route('api.thoughts.path', [
                'from' => $firstThought->id,
                'to' => $secondThought->id,
            ]))
            ->assertStatus(422);
    }

    private function createUserWithWorkspace(): User
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        Stream::factory()->for($space)->create(['position' => 1]);

        return $user;
    }
}

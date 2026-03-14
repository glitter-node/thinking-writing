<?php

namespace Tests\Feature;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmptyStatePagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function emptyStateRoutesProvider(): array
    {
        return [
            'graph' => ['/graph', 'graph.index'],
            'canvas' => ['/canvas', 'canvas.index'],
        ];
    }

    /**
     * @dataProvider emptyStateRoutesProvider
     */
    public function test_empty_state_pages_render_for_users_without_spaces(string $route, string $expectedView): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get($route)
            ->assertOk()
            ->assertViewIs('empty.spaces')
            ->assertSee('No spaces yet');
    }

    /**
     * @dataProvider emptyStateRoutesProvider
     */
    public function test_normal_pages_render_when_user_has_spaces(string $route, string $expectedView): void
    {
        $user = User::factory()->create();
        $space = Space::factory()->for($user)->create();
        Stream::factory()->for($space)->create(['position' => 1]);

        $response = $this->actingAs($user)->get($route);

        $response
            ->assertOk()
            ->assertViewIs($expectedView)
            ->assertDontSee('No spaces yet');
    }

    /**
     * @dataProvider emptyStateRoutesProvider
     */
    public function test_guest_users_are_redirected_from_empty_state_pages(string $route, string $expectedView): void
    {
        $this->get($route)
            ->assertRedirect(route('login'));
    }
}

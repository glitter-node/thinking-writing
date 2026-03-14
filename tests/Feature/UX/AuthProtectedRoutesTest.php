<?php

namespace Tests\Feature\UX;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthProtectedRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function protectedRoutesProvider(): array
    {
        return [
            'graph' => ['/graph'],
            'canvas' => ['/canvas'],
            'spaces' => ['/spaces'],
            'projects' => ['/projects'],
            'profile' => ['/profile'],
            'emergence' => ['/emergence'],
            'export' => ['/export/thoughts'],
        ];
    }

    /**
     * @dataProvider protectedRoutesProvider
     */
    public function test_guests_are_redirected_from_protected_routes(string $route): void
    {
        $this->get($route)
            ->assertRedirect(route('login'));
    }
}

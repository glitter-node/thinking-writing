<?php

namespace Tests\Feature\UX;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestRedirectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function guestRoutesProvider(): array
    {
        return [
            'login' => ['/login'],
            'register' => ['/register'],
            'forgot password' => ['/forgot-password'],
            'reset password' => ['/reset-password/test-token'],
        ];
    }

    /**
     * @dataProvider guestRoutesProvider
     */
    public function test_authenticated_users_are_redirected_from_guest_only_routes(string $route): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get($route)
            ->assertRedirect(route('dashboard', absolute: false));
    }
}

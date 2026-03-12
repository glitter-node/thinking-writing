<?php

namespace Tests\Feature\Auth;

use App\Services\GoogleIdentityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_creates_or_finds_a_user_and_signs_them_in(): void
    {
        $service = Mockery::mock(GoogleIdentityService::class);
        $service->shouldReceive('verifyIdToken')
            ->once()
            ->with('google-id-token')
            ->andReturn([
                'email' => 'google-user@example.com',
                'name' => 'Google User',
                'sub' => 'google-subject',
            ]);

        $this->app->instance(GoogleIdentityService::class, $service);

        $response = $this->postJson(route('auth.google'), [
            'credential' => 'google-id-token',
        ]);

        $response->assertOk()->assertJson([
            'redirect' => route('dashboard', absolute: false),
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google-user@example.com',
            'name' => 'Google User',
        ]);
        $this->assertNotNull(\App\Models\User::where('email', 'google-user@example.com')->value('email_verified_at'));
    }
}

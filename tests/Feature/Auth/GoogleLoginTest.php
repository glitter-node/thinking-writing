<?php

namespace Tests\Feature\Auth;

use App\Models\AuthIdentity;
use App\Models\User;
use App\Services\GoogleOneTapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_creates_or_finds_a_user_and_signs_them_in(): void
    {
        $service = Mockery::mock(GoogleOneTapService::class);
        $service->shouldReceive('verify')
            ->once()
            ->with('google-id-token')
            ->andReturn([
                'email' => 'google-user@example.com',
                'name' => 'Google User',
                'picture' => 'https://example.com/avatar.png',
                'sub' => 'google-subject',
            ]);

        $this->app->instance(GoogleOneTapService::class, $service);

        $response = $this->postJson(route('auth.google-one-tap'), [
            'credential' => 'google-id-token',
        ]);

        $response->assertOk()->assertJson([
            'status' => 'ok',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google-user@example.com',
            'name' => 'Google User',
        ]);
        $this->assertNotNull(User::where('email', 'google-user@example.com')->value('email_verified_at'));
        $this->assertDatabaseHas('auth_identities', [
            'provider' => 'google',
            'provider_user_id' => 'google-subject',
            'email' => 'google-user@example.com',
        ]);
        $this->assertDatabaseHas('auth_identities', [
            'provider' => 'email',
            'provider_user_id' => 'google-user@example.com',
            'email' => 'google-user@example.com',
        ]);
    }

    public function test_google_login_reuses_existing_user_by_verified_email(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'existing-google@example.com',
            'name' => 'Existing User',
        ]);

        $service = Mockery::mock(GoogleOneTapService::class);
        $service->shouldReceive('verify')
            ->once()
            ->with('existing-google-token')
            ->andReturn([
                'email' => 'existing-google@example.com',
                'name' => 'Google Profile Name',
                'picture' => 'https://example.com/avatar.png',
                'sub' => 'google-subject',
            ]);

        $this->app->instance(GoogleOneTapService::class, $service);

        $response = $this->postJson(route('auth.google-one-tap'), [
            'credential' => 'existing-google-token',
        ]);

        $response->assertOk()->assertJson([
            'status' => 'ok',
        ]);

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertEquals(1, User::where('email', 'existing-google@example.com')->count());
        $this->assertEquals('Existing User', $user->fresh()->name);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertEquals(1, AuthIdentity::query()->where('provider', 'google')->count());
    }
}

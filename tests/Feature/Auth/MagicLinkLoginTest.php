<?php

namespace Tests\Feature\Auth;

use App\Mail\MagicLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MagicLinkLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_magic_link_request_sends_mail_and_login_link_authenticates_the_user(): void
    {
        Mail::fake();

        $response = $this->post(route('auth.magic-link'), [
            'email' => 'magic-user@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'A sign-in link has been sent to your email address.');

        $user = User::where('email', 'magic-user@example.com')->firstOrFail();

        Mail::assertSent(MagicLink::class);

        $magicUrl = URL::temporarySignedRoute(
            'auth.magic',
            now()->addMinutes(10),
            [
                'token' => hash_hmac('sha256', $user->email.'|'.$user->created_at?->timestamp, (string) config('app.key')),
                'email' => $user->email,
            ]
        );

        $loginResponse = $this->get($magicUrl);

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $loginResponse->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_expired_magic_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'expired-magic@example.com',
        ]);

        $expiredUrl = URL::temporarySignedRoute(
            'auth.magic',
            now()->subMinute(),
            [
                'token' => hash_hmac('sha256', $user->email.'|'.$user->created_at?->timestamp, (string) config('app.key')),
                'email' => $user->email,
            ]
        );

        $this->get($expiredUrl)->assertForbidden();
        $this->assertGuest();
    }
}

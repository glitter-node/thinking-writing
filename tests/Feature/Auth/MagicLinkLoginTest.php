<?php

namespace Tests\Feature\Auth;

use App\Mail\MagicLink;
use App\Models\MagicLinkToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MagicLinkLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_magic_link_request_sends_mail_and_login_link_authenticates_the_user(): void
    {
        Mail::fake();

        $response = $this->post(route('auth.magic.request'), [
            'email' => 'magic-user@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'A sign-in link has been sent to your email address.');

        $user = User::where('email', 'magic-user@example.com')->firstOrFail();

        $magicUrl = null;
        Mail::assertSent(MagicLink::class, function (MagicLink $mail) use (&$magicUrl): bool {
            $magicUrl = $mail->magicUrl;

            return str_contains($mail->magicUrl, '/auth/magic/verify');
        });

        $loginResponse = $this->get($magicUrl);

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $loginResponse->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('auth_identities', [
            'provider' => 'email',
            'provider_user_id' => $user->email,
            'email' => $user->email,
        ]);
        $this->assertNotNull(MagicLinkToken::query()->where('email', $user->email)->value('used_at'));
    }

    public function test_expired_magic_link_is_rejected(): void
    {
        Mail::fake();

        $this->post(route('auth.magic.request'), [
            'email' => 'expired-magic@example.com',
        ]);

        $magicUrl = null;
        Mail::assertSent(MagicLink::class, function (MagicLink $mail) use (&$magicUrl): bool {
            $magicUrl = $mail->magicUrl;

            return true;
        });

        $record = MagicLinkToken::query()->firstOrFail();
        $record->forceFill([
            'expires_at' => now()->subMinute(),
        ])->save();

        $this->get($magicUrl)->assertForbidden();
        $this->assertGuest();
    }

    public function test_magic_link_cannot_be_reused(): void
    {
        Mail::fake();

        $this->post(route('auth.magic.request'), [
            'email' => 'reused@example.com',
        ]);

        $magicUrl = null;
        Mail::assertSent(MagicLink::class, function (MagicLink $mail) use (&$magicUrl): bool {
            $magicUrl = $mail->magicUrl;

            return true;
        });

        $this->get($magicUrl)->assertRedirect(route('dashboard', absolute: false));
        $this->post(route('logout'));
        $this->get($magicUrl)->assertForbidden();
    }
}

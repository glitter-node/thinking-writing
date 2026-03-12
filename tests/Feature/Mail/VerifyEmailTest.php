<?php

namespace Tests\Feature\Mail;

use App\Mail\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VerifyEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_dispatches_the_verify_email_mailable(): void
    {
        Mail::fake();

        $response = $this->post(route('register'), [
            'name' => 'Mail Tester',
            'email' => 'mail-tester@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        Mail::assertSent(VerifyEmail::class, function (VerifyEmail $mail): bool {
            return $mail->hasTo('mail-tester@example.com')
                && str_contains($mail->verifyUrl, '/verify-email/');
        });
    }
}

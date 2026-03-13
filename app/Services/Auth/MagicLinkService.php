<?php

namespace App\Services\Auth;

use App\Mail\MagicLink;
use App\Models\MagicLinkToken;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MagicLinkService
{
    public function __construct(
        private readonly IdentityResolver $identityResolver,
        private readonly MailService $mailService,
    ) {}

    public function requestLink(string $email): void
    {
        $this->identityResolver->resolve(
            email: $email,
            provider: 'email',
            providerUserId: $email,
            metadata: ['link_email_identity' => true],
        );

        MagicLinkToken::query()
            ->where('email', $email)
            ->whereNull('used_at')
            ->delete();

        $plainToken = Str::random(64);
        $tokenHash = hash_hmac('sha256', $plainToken, $this->secret());

        MagicLinkToken::create([
            'email' => $email,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addMinutes($this->expireMinutes()),
        ]);

        $magicUrl = $this->temporarySignedRoute([
            'email' => $email,
            'token' => $plainToken,
        ]);

        $this->mailService->sendMailable(
            $email,
            new MagicLink($magicUrl, $email)
        );
    }

    public function consume(string $email, string $plainToken): User
    {
        $tokenHash = hash_hmac('sha256', $plainToken, $this->secret());

        return DB::transaction(function () use ($email, $tokenHash): User {
            $record = MagicLinkToken::query()
                ->where('email', $email)
                ->where('token_hash', $tokenHash)
                ->lockForUpdate()
                ->first();

            if ($record === null) {
                throw new HttpException(403, 'Invalid magic link token.');
            }

            if ($record->used_at !== null) {
                throw new HttpException(403, 'This magic link has already been used.');
            }

            if ($record->expires_at->isPast()) {
                throw new HttpException(403, 'This magic link has expired.');
            }

            $record->forceFill([
                'used_at' => now(),
            ])->save();

            return $this->identityResolver->resolve(
                email: $email,
                provider: 'email',
                providerUserId: $email,
                metadata: ['link_email_identity' => true],
            );
        });
    }

    private function temporarySignedRoute(array $parameters): string
    {
        $baseUrl = rtrim((string) env('BASE_URL', config('app.url')), '/');
        $defaultUrl = rtrim((string) config('app.url'), '/');

        URL::useOrigin($baseUrl !== '' ? $baseUrl : null);

        try {
            return URL::temporarySignedRoute(
                'auth.magic.verify',
                now()->addMinutes($this->expireMinutes()),
                $parameters
            );
        } finally {
            URL::useOrigin($defaultUrl !== '' ? $defaultUrl : null);
        }
    }

    private function expireMinutes(): int
    {
        return (int) config('auth.magic_links.expire_minutes', 10);
    }

    private function secret(): string
    {
        return (string) config('auth.magic_links.secret', config('app.key'));
    }
}

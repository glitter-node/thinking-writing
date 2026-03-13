<?php

namespace App\Services\Auth;

use App\Models\AuthIdentity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class IdentityResolver
{
    public function resolve(
        string $email,
        string $provider,
        ?string $providerUserId = null,
        ?string $name = null,
        array $metadata = [],
    ): User {
        return DB::transaction(function () use ($email, $provider, $providerUserId, $name, $metadata): User {
            $identity = null;

            if ($providerUserId !== null && $providerUserId !== '') {
                $identity = AuthIdentity::query()
                    ->where('provider', $provider)
                    ->where('provider_user_id', $providerUserId)
                    ->first();
            }

            $user = $identity?->user;

            if ($user === null) {
                $user = User::query()->where('email', $email)->first();
            }

            if ($user === null) {
                $user = User::create([
                    'name' => $name ?: $this->defaultNameFromEmail($email),
                    'email' => $email,
                    'password' => Hash::make(Str::random(40)),
                ]);
            }

            if (! $user->hasVerifiedEmail()) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            AuthIdentity::updateOrCreate(
                [
                    'provider' => $provider,
                    'provider_user_id' => $providerUserId,
                ],
                [
                    'user_id' => $user->getKey(),
                    'email' => $email,
                ]
            );

            if (($metadata['link_email_identity'] ?? false) === true) {
                AuthIdentity::updateOrCreate(
                    [
                        'provider' => 'email',
                        'provider_user_id' => $email,
                    ],
                    [
                        'user_id' => $user->getKey(),
                        'email' => $email,
                    ]
                );
            }

            return $user;
        });
    }

    private function defaultNameFromEmail(string $email): string
    {
        return Str::title(str_replace(['.', '_', '-'], ' ', Str::before($email, '@')));
    }
}

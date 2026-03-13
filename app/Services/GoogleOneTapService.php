<?php

namespace App\Services;

use Google\Client as GoogleClient;
use RuntimeException;

class GoogleOneTapService
{
    public function verify(string $credential): array
    {
        $clientId = (string) config('services.google.client_id');

        if ($clientId === '') {
            throw new RuntimeException('Missing GOOGLE_CLIENT_ID configuration.');
        }

        $client = new GoogleClient([
            'client_id' => $clientId,
        ]);

        $payload = $client->verifyIdToken($credential);

        if (! is_array($payload)) {
            throw new RuntimeException('Google ID token could not be verified.');
        }

        $issuer = (string) ($payload['iss'] ?? '');
        $audience = $payload['aud'] ?? null;
        $expiresAt = (int) ($payload['exp'] ?? 0);
        $email = (string) ($payload['email'] ?? '');
        $emailVerified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOL);

        if (! in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new RuntimeException('Invalid Google token issuer.');
        }

        if ($audience !== $clientId) {
            throw new RuntimeException('Invalid Google token audience.');
        }

        if ($expiresAt < now()->timestamp) {
            throw new RuntimeException('Expired Google token.');
        }

        if (! $emailVerified) {
            throw new RuntimeException('Google email is not verified.');
        }

        if ($email === '') {
            throw new RuntimeException('Google token did not include an email address.');
        }

        return [
            'email' => $email,
            'name' => (string) ($payload['name'] ?? ''),
            'picture' => (string) ($payload['picture'] ?? ''),
            'sub' => (string) ($payload['sub'] ?? ''),
        ];
    }
}

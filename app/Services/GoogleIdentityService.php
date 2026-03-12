<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleIdentityService
{
    public function verifyIdToken(string $credential): array
    {
        [$encodedHeader, $encodedPayload, $encodedSignature] = $this->decodeSegments($credential);

        $header = json_decode($this->base64UrlDecode($encodedHeader), true);
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);
        $signature = $this->base64UrlDecode($encodedSignature);

        if (! is_array($header) || ! is_array($payload)) {
            throw new RuntimeException('Malformed Google token payload.');
        }

        $clientId = (string) config('services.google.client_id');

        if ($clientId === '' || ($payload['aud'] ?? null) !== $clientId) {
            throw new RuntimeException('Invalid Google token audience.');
        }

        if (! in_array($payload['iss'] ?? null, ['https://accounts.google.com', 'accounts.google.com'], true)) {
            throw new RuntimeException('Invalid Google token issuer.');
        }

        if (($payload['exp'] ?? 0) < now()->timestamp) {
            throw new RuntimeException('Expired Google token.');
        }

        if (($payload['email_verified'] ?? false) !== true) {
            throw new RuntimeException('Google email is not verified.');
        }

        $certificate = $this->googleCertificateForKey($header['kid'] ?? null);

        $verified = openssl_verify(
            $encodedHeader.'.'.$encodedPayload,
            $signature,
            $certificate,
            OPENSSL_ALGO_SHA256
        );

        if ($verified !== 1) {
            throw new RuntimeException('Google token signature verification failed.');
        }

        return [
            'email' => (string) ($payload['email'] ?? ''),
            'name' => (string) ($payload['name'] ?? ''),
            'sub' => (string) ($payload['sub'] ?? ''),
        ];
    }

    private function decodeSegments(string $credential): array
    {
        $segments = explode('.', $credential);

        if (count($segments) !== 3) {
            throw new RuntimeException('Malformed Google credential.');
        }

        return $segments;
    }

    private function googleCertificateForKey(?string $kid): string
    {
        if ($kid === null || $kid === '') {
            throw new RuntimeException('Missing Google token key id.');
        }

        $jwks = Cache::remember('google.identity.jwks', now()->addHour(), function (): array {
            $response = Http::timeout(5)->acceptJson()->get(
                (string) config('services.google.jwks_url', 'https://www.googleapis.com/oauth2/v3/certs')
            );

            if (! $response->successful()) {
                throw new RuntimeException('Unable to fetch Google signing keys.');
            }

            return $response->json('keys', []);
        });

        foreach ($jwks as $key) {
            if (($key['kid'] ?? null) !== $kid) {
                continue;
            }

            if (! empty($key['x5c'][0])) {
                return "-----BEGIN CERTIFICATE-----\n"
                    .chunk_split($key['x5c'][0], 64, "\n")
                    ."-----END CERTIFICATE-----\n";
            }
        }

        throw new RuntimeException('Matching Google signing key not found.');
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;

        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        if ($decoded === false) {
            throw new RuntimeException('Unable to decode token segment.');
        }

        return $decoded;
    }
}

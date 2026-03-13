<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\IdentityResolver;
use App\Services\Auth\SessionAuthenticator;
use App\Services\GoogleOneTapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleOneTapController extends Controller
{
    public function store(
        Request $request,
        GoogleOneTapService $googleOneTapService,
        IdentityResolver $identityResolver,
        SessionAuthenticator $sessionAuthenticator,
    ): JsonResponse {
        $validated = $request->validate([
            'credential' => ['required', 'string'],
        ]);

        Log::info('Google One Tap request received.', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            $identity = $googleOneTapService->verify($validated['credential']);
        } catch (Throwable $exception) {
            Log::warning('Google One Tap verification failed.', [
                'message' => $exception->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Unable to verify the Google sign-in token.',
            ], 422);
        }

        $user = $identityResolver->resolve(
            email: $identity['email'],
            provider: 'google',
            providerUserId: $identity['sub'],
            name: $identity['name'] ?: null,
            metadata: ['link_email_identity' => true]
        );

        $sessionAuthenticator->createSession($request, $user);

        Log::info('Google One Tap session established.', [
            'user_id' => $user->getKey(),
            'email' => $user->email,
            'provider' => 'google',
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    }
}

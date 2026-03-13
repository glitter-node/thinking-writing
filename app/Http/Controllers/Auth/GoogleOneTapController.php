<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleOneTapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GoogleOneTapController extends Controller
{
    public function store(Request $request, GoogleOneTapService $googleOneTapService): JsonResponse
    {
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

        $user = User::where('email', $identity['email'])->first();

        if ($user === null) {
            $user = User::create([
                'name' => $identity['name'] ?: $this->defaultNameFromEmail($identity['email']),
                'email' => $identity['email'],
                'password' => Hash::make(Str::random(40)),
            ]);

            Log::info('Google One Tap created a new account.', [
                'email' => $user->email,
                'google_sub' => $identity['sub'],
            ]);
        } else {
            Log::info('Google One Tap reused an existing account.', [
                'email' => $user->email,
                'user_id' => $user->getKey(),
                'google_sub' => $identity['sub'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        Log::info('Google One Tap session established.', [
            'user_id' => $user->getKey(),
            'email' => $user->email,
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    }

    private function defaultNameFromEmail(string $email): string
    {
        return Str::title(str_replace(['.', '_', '-'], ' ', Str::before($email, '@')));
    }
}

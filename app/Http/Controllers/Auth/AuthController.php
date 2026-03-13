<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\MagicLinkService;
use App\Services\Auth\SessionAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function index(): View
    {
        return view('auth.request-access');
    }

    public function sendMagicLink(Request $request, MagicLinkService $magicLinkService): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        $magicLinkService->requestLink($validated['email']);

        return back()->with('status', 'A sign-in link has been sent to your email address.');
    }

    public function loginViaMagicLink(
        Request $request,
        MagicLinkService $magicLinkService,
        SessionAuthenticator $sessionAuthenticator,
    ): RedirectResponse {
        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'token' => ['nullable', 'string'],
        ]);

        $token = $validated['token'] ?: (string) $request->route('token', '');

        abort_if($token === '', 403);

        $user = $magicLinkService->consume($validated['email'], $token);
        $sessionAuthenticator->createSession($request, $user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}

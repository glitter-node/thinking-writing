<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MagicLink;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function index(): View
    {
        return view('auth.request-access');
    }

    public function sendMagicLink(Request $request, MailService $mailService): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $this->defaultNameFromEmail($validated['email']),
                'password' => Hash::make(Str::random(40)),
            ]
        );

        $magicUrl = $this->temporarySignedRoute(
            'auth.magic',
            now()->addMinutes(10),
            [
                'token' => $this->magicTokenFor($user),
                'email' => $user->email,
            ]
        );

        $mailService->sendMailable(
            $user->email,
            new MagicLink($magicUrl, $user->email)
        );

        return back()->with('status', 'A sign-in link has been sent to your email address.');
    }

    public function loginViaMagicLink(Request $request, string $token): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        abort_unless(hash_equals($this->magicTokenFor($user), $token), 403);

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function defaultNameFromEmail(string $email): string
    {
        return Str::title(str_replace(['.', '_', '-'], ' ', Str::before($email, '@')));
    }

    private function magicTokenFor(User $user): string
    {
        return hash_hmac(
            'sha256',
            $user->email.'|'.$user->created_at?->timestamp,
            (string) config('app.key')
        );
    }

    private function temporarySignedRoute(string $route, mixed $expiration, array $parameters = []): string
    {
        $baseUrl = rtrim((string) env('BASE_URL', config('app.url')), '/');
        $defaultUrl = rtrim((string) config('app.url'), '/');

        URL::useOrigin($baseUrl !== '' ? $baseUrl : null);

        try {
            return URL::temporarySignedRoute($route, $expiration, $parameters);
        } finally {
            URL::useOrigin($defaultUrl !== '' ? $defaultUrl : null);
        }
    }
}

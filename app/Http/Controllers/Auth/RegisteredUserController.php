<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, MailService $mailService): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $verifyUrl = $this->temporarySignedRoute('verification.verify', [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $mailService->sendMailable(
            $user->email,
            new VerifyEmail($verifyUrl, $user->email)
        );

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function temporarySignedRoute(string $route, array $parameters): string
    {
        $baseUrl = rtrim((string) env('BASE_URL', config('app.url')), '/');
        $defaultUrl = rtrim((string) config('app.url'), '/');

        URL::useOrigin($baseUrl !== '' ? $baseUrl : null);

        try {
            return URL::temporarySignedRoute($route, now()->addMinutes(60), $parameters);
        } finally {
            URL::useOrigin($defaultUrl !== '' ? $defaultUrl : null);
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use App\Services\MailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request, MailService $mailService): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $verifyUrl = $this->temporarySignedRoute('verification.verify', [
            'id' => $request->user()->getKey(),
            'hash' => sha1($request->user()->getEmailForVerification()),
        ]);

        $mailService->sendMailable(
            $request->user()->email,
            new VerifyEmail($verifyUrl, $request->user()->email)
        );

        return back()->with('status', 'verification-link-sent');
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

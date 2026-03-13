<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionAuthenticator
{
    public function createSession(Request $request, User $user): void
    {
        Auth::login($user, true);
        $request->session()->regenerate();
    }
}

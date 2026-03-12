<x-guest-layout>
    <div class="w-full max-w-[480px] rounded-3xl border border-white/10 bg-white/[0.04] p-8 shadow-2xl shadow-black/20">
        <div class="space-y-3 text-center">
            <p class="text-xs uppercase tracking-[0.24em] text-orange-200">Glitter Thought Write</p>
            <h1 class="font-['Space_Grotesk'] text-3xl font-bold text-stone-100">Request Access</h1>
            <p class="text-sm leading-6 text-stone-300">
                Use Google One Tap to sign in, or request an email link.
            </p>
        </div>

        <x-auth-session-status class="mt-6" :status="session('status')" />

        @if ($errors->any())
            <div class="mt-4 rounded-2xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mt-8 rounded-2xl border border-white/10 bg-black/20 p-5 text-center">
            <p class="text-sm font-medium text-stone-100">Google One Tap Sign-In</p>
            <p class="mt-2 text-sm leading-6 text-stone-300">
                Sign in instantly with your Google account.
            </p>
            @if (config('services.google.client_id'))
                <div id="g_id_onload" class="mt-5"></div>
                <div id="google-signin-status" class="mt-3 text-sm text-stone-400"></div>
            @else
                <p class="mt-4 rounded-xl border border-amber-400/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                    Google sign-in is unavailable until `GOOGLE_CLIENT_ID` is configured.
                </p>
            @endif
        </div>

        <div class="mt-6 rounded-2xl border border-white/10 bg-black/20 p-5">
            <p class="text-sm font-medium text-stone-100">Fallback: email sign-in link.</p>
            <form method="POST" action="{{ route('auth.email-link') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="email" class="text-sm text-stone-300">Email address</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        class="mt-2 w-full rounded-2xl border border-white/10 bg-stone-950/80 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-300/30"
                        placeholder="you@example.com"
                    >
                </div>
                <button
                    type="submit"
                    class="w-full rounded-2xl bg-orange-400 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-300"
                >
                    Send Link
                </button>
            </form>
        </div>

        <div class="mt-6 flex items-center justify-between text-sm text-stone-400">
            <a href="{{ route('register') }}" class="transition hover:text-orange-200">Create account</a>
            <button
                type="button"
                onclick="window.history.back()"
                aria-label="Cancel and return to previous page"
                class="rounded-xl bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-300"
            >
                Cancel
            </button>
        </div>
    </div>

    @if (config('services.google.client_id'))
        @push('scripts')
            <script src="https://accounts.google.com/gsi/client" async defer></script>
            <script>
                function handleCredentialResponse(response) {
                    const status = document.getElementById('google-signin-status');

                    status.textContent = 'Signing you in...';

                    fetch('{{ route('auth.google') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ credential: response.credential }),
                    })
                    .then(async (result) => {
                        const payload = await result.json();

                        if (!result.ok) {
                            throw new Error(payload.message || 'Google sign-in failed.');
                        }

                        window.location.href = payload.redirect;
                    })
                    .catch((error) => {
                        status.textContent = error.message;
                    });
                }

                window.addEventListener('load', () => {
                    google.accounts.id.initialize({
                        client_id: '{{ config('services.google.client_id') }}',
                        callback: handleCredentialResponse,
                    });

                    google.accounts.id.prompt();
                });
            </script>
        @endpush
    @endif
</x-guest-layout>

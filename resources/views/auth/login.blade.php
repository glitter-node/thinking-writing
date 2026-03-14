<x-guest-layout>
    <div class="space-y-8">
        <div class="space-y-3">
            <p class="auth-kicker">{{ __('Welcome back') }}</p>
            <h2 class="auth-title">{{ __('Sign in to your workspace') }}</h2>
            <p class="auth-copy">
                {{ __('Open your thinking graph, review active threads, and continue shaping connected ideas.') }}
            </p>
        </div>

        <x-auth-session-status class="auth-status" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6" data-auth-form>
        @csrf

            <div>
                <x-input-label class="auth-label" for="email" :value="__('Email')" />
                <x-text-input
                    id="email"
                    class="auth-input"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    aria-describedby="email-error"
                />
                <x-input-error :messages="$errors->get('email')" class="auth-error-list" id="email-error" />
            </div>

            <div>
                <div class="flex items-center justify-between gap-3">
                    <x-input-label class="auth-label" for="password" :value="__('Password')" />
                    @if (Route::has('password.request'))
                        <a class="auth-secondary-link text-xs" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <div class="auth-input-with-action">
                    <x-text-input
                        id="password"
                        class="auth-input pr-20"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        aria-describedby="password-error"
                        data-password-field
                    />
                    <button
                        type="button"
                        class="auth-input-action"
                        data-password-toggle
                        data-show-label="{{ __('Show') }}"
                        data-hide-label="{{ __('Hide') }}"
                        aria-controls="password"
                        aria-pressed="false"
                    >
                        {{ __('Show') }}
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="auth-error-list" id="password-error" />
            </div>

            <label for="remember_me" class="flex items-center gap-3 text-sm text-stone-300">
                <input id="remember_me" type="checkbox" class="auth-checkbox" name="remember">
                <span>{{ __('Keep me signed in on this device') }}</span>
            </label>

            <div class="space-y-4 pt-2">
                <button type="submit" class="auth-button" data-submit-button>
                    <span data-submit-label>{{ __('Sign in') }}</span>
                    <span class="hidden" data-submit-loading>{{ __('Signing in...') }}</span>
                </button>

                <div class="space-y-3 border-t border-white/10 pt-4 text-center">
                    <p class="text-sm text-stone-400">
                        {{ __("Don't have an account yet?") }}
                        <a class="auth-secondary-link" href="{{ route('register') }}">{{ __('Create one') }}</a>
                    </p>
                    <button
                        type="button"
                        data-back-button
                        aria-label="Cancel and return to previous page"
                        class="auth-secondary-link"
                    >
                        {{ __('Cancel and go back') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>

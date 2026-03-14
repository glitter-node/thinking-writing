<x-guest-layout>
    <div class="space-y-8">
        <div class="space-y-3">
            <p class="auth-kicker">{{ __('Start capturing') }}</p>
            <h2 class="auth-title">{{ __('Create your workspace') }}</h2>
            <p class="auth-copy">
                {{ __('Set up your account to capture thoughts, connect ideas, and build a durable personal knowledge graph.') }}
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-6" data-auth-form>
        @csrf

            <div>
                <x-input-label class="auth-label" for="name" :value="__('Name')" />
                <x-text-input
                    id="name"
                    class="auth-input"
                    type="text"
                    name="name"
                    :value="old('name')"
                    required
                    autofocus
                    autocomplete="name"
                    aria-describedby="name-error"
                />
                <x-input-error :messages="$errors->get('name')" class="auth-error-list" id="name-error" />
            </div>

            <div>
                <x-input-label class="auth-label" for="email" :value="__('Email')" />
                <x-text-input
                    id="email"
                    class="auth-input"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autocomplete="username"
                    aria-describedby="email-error"
                />
                <x-input-error :messages="$errors->get('email')" class="auth-error-list" id="email-error" />
            </div>

            <div>
                <x-input-label class="auth-label" for="password" :value="__('Password')" />
                <div class="auth-input-with-action">
                    <x-text-input
                        id="password"
                        class="auth-input pr-20"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
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

            <div>
                <x-input-label class="auth-label" for="password_confirmation" :value="__('Confirm password')" />
                <div class="auth-input-with-action">
                    <x-text-input
                        id="password_confirmation"
                        class="auth-input pr-20"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        aria-describedby="password-confirmation-error"
                        data-password-field
                    />
                    <button
                        type="button"
                        class="auth-input-action"
                        data-password-toggle
                        data-show-label="{{ __('Show') }}"
                        data-hide-label="{{ __('Hide') }}"
                        aria-controls="password_confirmation"
                        aria-pressed="false"
                    >
                        {{ __('Show') }}
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error-list" id="password-confirmation-error" />
            </div>

            <div class="space-y-4 pt-2">
                <button type="submit" class="auth-button" data-submit-button>
                    <span data-submit-label>{{ __('Create account') }}</span>
                    <span class="hidden" data-submit-loading>{{ __('Creating account...') }}</span>
                </button>

                <div class="space-y-3 border-t border-white/10 pt-4 text-center">
                    <p class="text-sm text-stone-400">
                        {{ __('Already have an account?') }}
                        <a class="auth-secondary-link" href="{{ route('login') }}">
                            {{ __('Sign in') }}
                        </a>
                    </p>
                    @if (Route::has('password.request'))
                        <p class="text-sm text-stone-400">
                            <a class="auth-secondary-link" href="{{ route('password.request') }}">
                                {{ __('Need help resetting a password?') }}
                            </a>
                        </p>
                    @endif
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

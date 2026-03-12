<nav x-data="{ open: false }" class="border-b border-white/10 bg-stone-950/70 backdrop-blur">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <a href="{{ route('spaces.index') }}" class="flex items-center gap-3">
                <x-application-logo class="h-10 w-10 text-orange-300" />
                <div>
                    <p class="font-['Space_Grotesk'] text-lg font-bold tracking-[0.08em] text-stone-50">Glitter Thought Write</p>
                    <p class="text-xs uppercase tracking-[0.28em] text-stone-400">Thinking workspace</p>
                </div>
            </a>

            <div class="hidden items-center gap-2 sm:flex">
                <x-nav-link :href="route('spaces.index')" :active="request()->routeIs('spaces.*')">
                    {{ __('Spaces') }}
                </x-nav-link>
                <x-nav-link :href="route('graph.index')" :active="request()->routeIs('graph.*') || request()->routeIs('api.thoughts.*')">
                    {{ __('Graph Explorer') }}
                </x-nav-link>
                <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                    {{ __('Profile') }}
                </x-nav-link>
            </div>
        </div>

        <div class="hidden items-center gap-4 sm:flex">
            <div class="text-right">
                <p class="text-sm font-semibold text-stone-100">{{ Auth::user()->name }}</p>
                <p class="text-xs text-stone-400">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-stone-200 transition hover:border-orange-300/40 hover:text-orange-200">
                    {{ __('Log out') }}
                </button>
            </form>
        </div>

        <button @click="open = ! open" class="inline-flex items-center rounded-full border border-white/10 p-2 text-stone-200 sm:hidden">
            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div x-show="open" x-cloak class="border-t border-white/10 bg-stone-950/95 sm:hidden">
        <div class="space-y-2 px-4 py-4">
            <x-responsive-nav-link :href="route('spaces.index')" :active="request()->routeIs('spaces.*')">
                {{ __('Spaces') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('graph.index')" :active="request()->routeIs('graph.*') || request()->routeIs('api.thoughts.*')">
                {{ __('Graph Explorer') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                {{ __('Profile') }}
            </x-responsive-nav-link>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-2xl border border-white/10 px-4 py-3 text-left text-sm text-stone-200">
                    {{ __('Log out') }}
                </button>
            </form>
        </div>
    </div>
</nav>

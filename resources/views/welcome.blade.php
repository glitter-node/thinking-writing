<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="application-name" content="Glitter Thought Write">
        <meta name="application-version" content="{{ app_version() }}">
        <meta name="description" content="A personal thinking workspace for capturing and evolving ideas.">
        <title>{{ config('app.name', 'Glitter Thought Write') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700&family=dm-sans:400,500,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-['DM_Sans'] antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(251,146,60,0.28),_transparent_26%),radial-gradient(circle_at_80%_20%,_rgba(20,184,166,0.2),_transparent_24%),linear-gradient(180deg,_#1c1917,_#09090b)] px-4 py-8 text-stone-100 sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-12">
                <header class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <x-application-logo class="h-10 w-10 text-orange-300" />
                        <div>
                            <p class="font-['Space_Grotesk'] text-lg font-bold tracking-[0.08em]">Glitter Thought Write</p>
                            <p class="text-xs uppercase tracking-[0.3em] text-stone-400">Personal idea board</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="rounded-full border border-white/10 px-4 py-2 text-sm text-stone-200 transition hover:border-orange-300/40 hover:text-orange-200">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-orange-300 px-4 py-2 text-sm font-semibold text-stone-950 transition hover:bg-orange-200">Start capturing</a>
                    </div>
                </header>

                <section class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
                    <div class="space-y-6">
                        <span class="inline-flex rounded-full border border-orange-300/30 bg-orange-300/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-orange-200">Laravel 12 + Blade + Alpine</span>
                        <h1 class="max-w-3xl font-['Space_Grotesk'] text-5xl font-bold leading-tight text-stone-50 sm:text-6xl">
                            Capture thoughts fast. Shape them into motion.
                        </h1>
                        <p class="max-w-2xl text-lg text-stone-300">
                            Glitter Thought Write is a personal thinking workspace for capturing, evolving, and connecting thoughts. Spaces group your work, streams keep flow visible, and drag-and-drop keeps the board fluid.
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('register') }}" class="rounded-full bg-orange-300 px-6 py-3 font-semibold text-stone-950 transition hover:bg-orange-200">Create your board</a>
                            <a href="{{ route('login') }}" class="rounded-full border border-white/10 px-6 py-3 font-semibold text-stone-100 transition hover:border-teal-300/40 hover:text-teal-200">Open existing workspace</a>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="w-full rounded-3xl border border-white/10 bg-white/[0.04] p-8 shadow-2xl shadow-black/20">
                            <div class="space-y-3 text-center">
                                <p class="text-xs uppercase tracking-[0.24em] text-orange-200">Glitter Thought Write</p>
                                <h2 class="font-['Space_Grotesk'] text-3xl font-bold text-stone-100">Open your workspace</h2>
                                <p class="text-sm leading-6 text-stone-300">
                                    Sign in with your account or create a new workspace to start capturing ideas.
                                </p>
                            </div>
                            <div class="mt-8 grid gap-3 sm:grid-cols-2">
                                <a href="{{ route('login') }}" class="rounded-2xl border border-white/10 px-4 py-3 text-center text-sm font-semibold text-stone-100 transition hover:border-orange-300/40 hover:text-orange-200">
                                    Log in
                                </a>
                                <a href="{{ route('register') }}" class="rounded-2xl bg-orange-400 px-4 py-3 text-center text-sm font-semibold text-stone-950 transition hover:bg-orange-300">
                                    Create account
                                </a>
                            </div>
                        </div>

                        <div class="glass-panel overflow-hidden p-4">
                            <div class="rounded-[2rem] border border-white/10 bg-stone-950/70 p-4 shadow-2xl shadow-black/30">
                                <div class="mb-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm uppercase tracking-[0.3em] text-stone-500">Preview</p>
                                        <h2 class="font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Launch ideas</h2>
                                    </div>
                                    <span class="rounded-full bg-teal-300/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-teal-200">3 streams</span>
                                </div>
                                <div class="grid gap-4 md:grid-cols-3">
                                    @foreach ([
                                        'Inbox' => [['New AI note capture', 'high'], ['Founder memo clean-up', 'medium']],
                                        'In Progress' => [['Voice note parser', 'high'], ['Tag clustering pass', 'low']],
                                        'Done' => [['Board search UX', 'medium']],
                                    ] as $streamName => $cards)
                                        <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                                            <div class="mb-3 flex items-center justify-between">
                                                <h3 class="font-semibold text-stone-100">{{ $streamName }}</h3>
                                                <span class="text-xs text-stone-500">{{ count($cards) }}</span>
                                            </div>
                                            <div class="space-y-3">
                                                @foreach ($cards as [$title, $priority])
                                                    <div class="rounded-2xl border border-white/10 bg-stone-900/80 p-3">
                                                        <span class="priority-pill {{ $priority === 'high' ? 'bg-rose-400/15 text-rose-200' : ($priority === 'medium' ? 'bg-amber-300/15 text-amber-200' : 'bg-teal-300/15 text-teal-200') }}">{{ $priority }}</span>
                                                        <p class="mt-3 text-sm text-stone-200">{{ $title }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>

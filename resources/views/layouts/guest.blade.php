<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="application-name" content="Glitter Thought Write">
        <meta name="application-version" content="{{ app_version() }}">
        <meta name="description" content="A personal thinking workspace for capturing and evolving ideas.">

        <title>{{ config('app.name', 'Glitter Thought Write') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-stone-100 antialiased">
        <main class="auth-shell">
            <div class="auth-layout">
                <section class="auth-brand">
                    <a href="/" class="inline-flex items-center gap-3">
                        <x-application-logo class="h-12 w-12 text-orange-300" />
                        <div>
                            <p class="font-['Space_Grotesk'] text-xl font-bold text-stone-50">Glitter Thought Write</p>
                            <p class="mt-1 text-sm uppercase tracking-[0.28em] text-stone-500">Personal thinking graph</p>
                        </div>
                    </a>

                    <div class="mt-12 space-y-6">
                        <div>
                            <p class="auth-kicker">Thinking workspace</p>
                            <h1 class="mt-4 font-['Space_Grotesk'] text-5xl font-bold leading-tight text-stone-50">
                                Capture thoughts, connect ideas, and keep momentum.
                            </h1>
                            <p class="mt-4 max-w-xl text-base leading-7 text-stone-300">
                                A focused workspace for building a personal idea graph, tracking evolution, and turning fragments into durable knowledge.
                            </p>
                        </div>

                        <div class="grid gap-4 text-sm text-stone-300 sm:grid-cols-3">
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-[0.24em] text-orange-200">Capture</p>
                                <p class="mt-2 leading-6">Write quickly and keep the thread of thought alive.</p>
                            </div>
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-[0.24em] text-teal-200">Connect</p>
                                <p class="mt-2 leading-6">Link related ideas into a graph you can explore.</p>
                            </div>
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-[0.24em] text-stone-300">Evolve</p>
                                <p class="mt-2 leading-6">See versions, syntheses, and emerging patterns over time.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="auth-card">
                    {{ $slot }}
                </section>
            </div>
        </main>
        <script src="{{ asset('js/auth-ui.js') }}" defer></script>
        <script type="module" src="{{ asset('js/architecture-diagrams.js') }}"></script>
        @stack('scripts')
    </body>
</html>

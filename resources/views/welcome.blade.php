<!DOCTYPE html>
<html lang="en">
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
                <header class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-center gap-3">
                        <x-application-logo class="h-10 w-10 text-orange-300" />
                        <div class="min-w-0">
                            <p class="truncate font-['Space_Grotesk'] text-base font-bold tracking-[0.04em] sm:text-lg sm:tracking-[0.08em]">Glitter Thought Write</p>
                            <p class="text-xs uppercase tracking-[0.24em] text-stone-400 sm:tracking-[0.3em]">Personal thinking graph</p>
                        </div>
                    </div>
                    @if(false)
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="rounded-full border border-white/10 px-4 py-2 text-sm text-stone-200 transition hover:border-orange-300/40 hover:text-orange-200">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-orange-300 px-4 py-2 text-sm font-semibold text-stone-950 transition hover:bg-orange-200">Start capturing</a>
                    </div>
                    @endif
                </header>

                <section class="space-y-8">
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:items-end">
                        <div class="min-w-0 space-y-6">
                            <span class="inline-block max-w-full rounded-full border border-orange-300/30 bg-orange-300/10 px-4 py-2 text-xs font-semibold uppercase leading-relaxed tracking-[0.18em] text-orange-200 sm:tracking-[0.3em]">Personal thinking graph workspace</span>
                            <h1 class="max-w-4xl font-['Space_Grotesk'] text-4xl font-bold leading-tight text-stone-50 sm:text-6xl">
                                Capture thoughts. Connect them. Watch ideas evolve.
                            </h1>
                            <p class="max-w-2xl text-lg text-stone-300">
                                Turn scattered notes into a living knowledge graph.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('register') }}" class="rounded-full bg-orange-300 px-6 py-3 font-semibold text-stone-950 transition hover:bg-orange-200">Start your graph</a>
                                <a href="{{ route('login') }}" class="rounded-full border border-white/10 px-6 py-3 font-semibold text-stone-100 transition hover:border-teal-300/40 hover:text-teal-200">Open workspace</a>
                            </div>
                        </div>

                        <div class="glass-panel min-w-0 p-6">
                            <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Thinking model</p>
                            <div class="mt-5 rounded-[1.5rem] border border-white/10 bg-stone-950/60 p-4">
                                <div class="flex justify-center overflow-x-auto">
                                    <svg
                                        width="260"
                                        height="120"
                                        viewBox="0 0 260 120"
                                        class="h-auto w-full max-w-[260px] text-xs"
                                        aria-label="Thinking model graph preview"
                                        role="img"
                                    >
                                        <line x1="40" y1="40" x2="120" y2="40" stroke="#6ea8ff" stroke-opacity="0.75" />
                                        <line x1="120" y1="40" x2="90" y2="80" stroke="#6ea8ff" stroke-opacity="0.75" />
                                        <line x1="120" y1="40" x2="180" y2="80" stroke="#6ea8ff" stroke-opacity="0.75" />

                                        <circle cx="40" cy="40" r="8" fill="#1f2937" stroke="rgba(255,255,255,0.12)" />
                                        <circle cx="120" cy="40" r="8" fill="#1f2937" stroke="rgba(255,255,255,0.12)" />
                                        <circle cx="90" cy="80" r="8" fill="#1f2937" stroke="rgba(255,255,255,0.12)" />
                                        <circle cx="180" cy="80" r="8" fill="#1f2937" stroke="rgba(255,255,255,0.12)" />

                                        <text x="40" y="26" fill="#e7e5e4" font-size="10" text-anchor="middle">Thought A</text>
                                        <text x="120" y="26" fill="#e7e5e4" font-size="10" text-anchor="middle">Thought B</text>
                                        <text x="90" y="102" fill="#e7e5e4" font-size="10" text-anchor="middle">Insight</text>
                                        <text x="180" y="102" fill="#e7e5e4" font-size="10" text-anchor="middle">Project</text>
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-5 text-sm leading-6 text-stone-400">
                                Capture one thought at a time, connect what matters, and let larger patterns emerge from the network.
                            </p>
                        </div>
                    </div>

                    <div class="glass-panel overflow-hidden p-4">
                        <div class="grid gap-6 rounded-[2rem] border border-white/10 bg-stone-950/70 p-4 shadow-2xl shadow-black/30 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] lg:p-6">
                            <div class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-stone-900/80">
                                <img src="{{ asset('img/ThoughtGraph.png') }}" alt="ThinkWrite thought graph view" class="h-full w-full object-cover object-top" />
                            </div>
                            <div class="flex flex-col justify-center space-y-5">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.3em] text-stone-500">Core interface</p>
                                    <h2 class="mt-2 font-['Space_Grotesk'] text-3xl font-bold text-stone-50">See your thinking as a graph</h2>
                                </div>
                                <p class="text-base leading-7 text-stone-300">
                                    Thoughts become nodes. Relationships become structure. Instead of losing ideas in a flat list, you can follow how one thought leads to another.
                                </p>
                                <div class="flex flex-wrap gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-stone-300">
                                    <span class="rounded-full border border-white/10 bg-white/5 px-4 py-2">Capture</span>
                                    <span class="rounded-full border border-orange-300/20 bg-orange-300/10 px-4 py-2 text-orange-200">Connect</span>
                                    <span class="rounded-full border border-teal-300/20 bg-teal-300/10 px-4 py-2 text-teal-200">Evolve</span>
                                </div>
                                <div class="grid gap-2 text-sm text-stone-400 sm:grid-cols-3">
                                    <p><span class="text-stone-200">Capture</span> → write a thought</p>
                                    <p><span class="text-orange-200">Connect</span> → link related ideas</p>
                                    <p><span class="text-teal-200">Evolve</span> → turn insights into projects</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="max-w-3xl space-y-3">
                            <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Three views, one thinking system</p>
                            <h2 class="font-['Space_Grotesk'] text-3xl font-bold text-stone-50 sm:text-4xl">Different interfaces for different stages of thought</h2>
                            <p class="text-base leading-7 text-stone-300">
                                Graph view reveals relationships, canvas view opens up exploration, and board view helps organize what needs action.
                            </p>
                        </div>

                        <div class="grid gap-6 xl:grid-cols-3">
                            <article class="glass-panel overflow-hidden p-4">
                                <div class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-stone-950/70">
                                    <img src="{{ asset('img/ThoughtGraph.png') }}" alt="ThinkWrite graph view preview" class="h-64 min-h-[16rem] w-full object-cover object-top md:h-64" />
                                </div>
                                <div class="mt-4 space-y-2">
                                    <p class="text-xs uppercase tracking-[0.28em] text-orange-200">Graph View</p>
                                    <h3 class="font-['Space_Grotesk'] text-2xl font-bold text-stone-50">See how ideas connect</h3>
                                    <p class="text-sm leading-6 text-stone-400">See how ideas connect, branch, and converge into larger structures.</p>
                                </div>
                            </article>

                            <article class="glass-panel overflow-hidden p-4">
                                <div class="rounded-[1.5rem] border border-white/10 bg-stone-950/70 p-4">
                                    <div class="relative h-64 overflow-hidden rounded-[1.25rem] border border-cyan-300/10 bg-[radial-gradient(circle_at_20%_20%,_rgba(45,212,191,0.16),_transparent_22%),radial-gradient(circle_at_80%_30%,_rgba(251,146,60,0.14),_transparent_20%),linear-gradient(180deg,_rgba(24,24,27,0.95),_rgba(12,10,9,0.95))]">
                                        <div class="absolute inset-0 flex items-center justify-center overflow-hidden px-2 py-3">
                                            <div class="origin-center scale-75 transform sm:scale-90 md:scale-100">
                                                {{-- Keep these coordinate utilities in Blade so Tailwind includes them in production builds. --}}
                                                <div class="relative h-64 w-80 sm:w-[22rem] md:w-full">
                                                    <div class="absolute left-[8%] top-[16%] w-28 max-w-[8rem] rounded-2xl border border-white/10 bg-stone-900/85 p-3 text-xs leading-5 text-stone-200 shadow-lg shadow-black/20 sm:w-32 sm:max-w-[8.5rem] sm:text-sm sm:leading-6 break-words">Map the argument</div>
                                                    <div class="absolute right-[10%] top-[26%] w-32 max-w-[8.5rem] rounded-2xl border border-white/10 bg-stone-900/85 p-3 text-xs leading-5 text-stone-200 shadow-lg shadow-black/20 sm:w-36 sm:max-w-[9rem] sm:text-sm sm:leading-6 break-words">Open question</div>
                                                    <div class="absolute bottom-[16%] left-[28%] w-36 max-w-[10rem] rounded-2xl border border-white/10 bg-stone-900/85 p-3 text-xs leading-5 text-stone-200 shadow-lg shadow-black/20 sm:w-40 sm:max-w-[10rem] sm:text-sm sm:leading-6 break-words">Possible synthesis</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 space-y-2">
                                    <p class="text-xs uppercase tracking-[0.28em] text-teal-200">Canvas View</p>
                                    <h3 class="font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Explore spatially</h3>
                                    <p class="text-sm leading-6 text-stone-400">Spread thoughts out, test directions, and work through ideas in open space.</p>
                                </div>
                            </article>

                            <article class="glass-panel overflow-hidden p-4">
                                <div class="rounded-[1.5rem] border border-white/10 bg-stone-950/70 p-4">
                                    <div class="grid min-h-[16rem] gap-3 sm:grid-cols-2 md:h-64 md:grid-cols-3">
                                        @foreach ([
                                            'Inbox' => [['Capture founder note', 'high'], ['Tag edge cases', 'medium']],
                                            'Shaping' => [['Draft synthesis', 'high'], ['Review links', 'low']],
                                            'Ready' => [['Publish brief', 'medium']],
                                        ] as $streamName => $cards)
                                            <div class="rounded-3xl border border-white/10 bg-white/5 p-3">
                                                <div class="mb-3 flex items-center justify-between">
                                                    <h3 class="text-sm font-semibold text-stone-100">{{ $streamName }}</h3>
                                                    <span class="text-xs text-stone-500">{{ count($cards) }}</span>
                                                </div>
                                                <div class="space-y-2">
                                                    @foreach ($cards as [$title, $priority])
                                                        <div class="rounded-2xl border border-white/10 bg-stone-900/80 p-3">
                                                            <span class="priority-pill {{ $priority === 'high' ? 'bg-rose-400/15 text-rose-200' : ($priority === 'medium' ? 'bg-amber-300/15 text-amber-200' : 'bg-teal-300/15 text-teal-200') }}">{{ $priority }}</span>
                                                            <p class="mt-2 text-xs text-stone-200">{{ $title }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mt-4 space-y-2">
                                    <p class="text-xs uppercase tracking-[0.28em] text-stone-300">Board View</p>
                                    <h3 class="font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Organize the work</h3>
                                    <p class="text-sm leading-6 text-stone-400">Turn active thoughts into clear lanes when it is time to sort, review, and move forward.</p>
                                </div>
                            </article>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-3">
                        <div class="glass-panel p-6">
                            <p class="text-xs uppercase tracking-[0.28em] text-orange-200">Nodes</p>
                            <h3 class="mt-3 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Every idea starts as a thought</h3>
                            <p class="mt-3 text-sm leading-6 text-stone-400">Capture quickly, keep momentum, and give each thought a place in the wider system.</p>
                        </div>
                        <div class="glass-panel p-6">
                            <p class="text-xs uppercase tracking-[0.28em] text-teal-200">Links</p>
                            <h3 class="mt-3 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Ideas become networks</h3>
                            <p class="mt-3 text-sm leading-6 text-stone-400">Relationships between thoughts reveal context, tension, and paths worth exploring.</p>
                        </div>
                        <div class="glass-panel p-6">
                            <p class="text-xs uppercase tracking-[0.28em] text-stone-300">Evolution</p>
                            <h3 class="mt-3 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Ideas evolve into knowledge</h3>
                            <p class="mt-3 text-sm leading-6 text-stone-400">ThinkWrite helps ideas mature from fragments into connected understanding.</p>
                        </div>
                    </div>

                    <section class="glass-panel space-y-4 p-4 sm:space-y-5 sm:p-6">
                        <div class="max-w-3xl space-y-3">
                            <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Architecture</p>
                            <h2 class="font-['Space_Grotesk'] text-3xl font-bold text-stone-50 sm:text-4xl">Thought Domain Event Architecture</h2>
                            <p class="text-base leading-7 text-stone-300">
                                The thought lifecycle is event-driven. Create, placeholder, evolution, synthesis, link, and delete actions dispatch domain events that update versions, activity logs, graph indexes, tag indexes, and cooccurrence signals without concentrating all of that work in one service.
                            </p>
                            <p class="text-sm leading-6 text-stone-400">
                                This architecture ensures every change in your thinking automatically updates your knowledge graph.
                            </p>
                        </div>

                        <div class="flex justify-center overflow-x-auto rounded-[1.75rem] border border-white/10 bg-stone-950/70 p-3 sm:p-4 shadow-[0_0_60px_rgba(99,102,241,0.08)]">
                            <pre class="mermaid min-h-[220px] w-full text-sm text-stone-200 sm:min-h-[300px] md:min-h-[360px]" id="thought-domain-event-flow"></pre>
                        </div>
                    </section>
                </section>
            </div>
        </div>
        <script type="module" src="{{ asset('js/architecture-diagrams.js') }}"></script>
    </body>
</html>

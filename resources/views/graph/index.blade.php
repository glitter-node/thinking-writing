<x-app-layout>
    @vite(['resources/js/graph-explorer.js'])

    <div class="mx-auto max-w-7xl px-4 pb-12 pt-8 sm:px-6 lg:px-8">
        <div class="glass-panel px-6 py-5">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <a href="{{ route('spaces.show', $currentSpace) }}" class="text-xs uppercase tracking-[0.28em] text-stone-400 transition hover:text-orange-200">Back to board</a>
                    <h1 class="mt-3 font-['Space_Grotesk'] text-4xl font-bold text-stone-50">Graph Explorer</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-300">Explore thought relationships inside {{ $currentSpace->title }} with an interactive Cytoscape knowledge graph.</p>
                    @if ($focusThought)
                        <p class="mt-3 text-xs uppercase tracking-[0.22em] text-orange-200">Focus mode: thought {{ $focusThought->id }}</p>
                    @endif
                    <a href="{{ route('canvas.index', ['space' => $currentSpace->id]) }}" class="mt-4 inline-flex rounded-full border border-cyan-300/30 bg-cyan-300/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-100 transition hover:bg-cyan-300/15">Open spatial canvas</a>
                </div>

                <form method="GET" action="{{ route('graph.index') }}" class="grid w-full gap-3 sm:w-auto sm:min-w-[280px]">
                    <label for="space" class="text-xs uppercase tracking-[0.24em] text-stone-500">Space</label>
                    <select id="space" name="space" onchange="this.form.submit()" class="rounded-2xl border border-white/10 bg-stone-950/60 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
                        @foreach ($spaces as $space)
                            <option value="{{ $space->id }}" @selected($space->is($currentSpace))>{{ $space->title }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <section class="glass-panel overflow-hidden p-0">
                <div class="border-b border-white/10 px-6 py-4">
                    <p class="text-xs uppercase tracking-[0.26em] text-stone-500">Cytoscape canvas</p>
                    <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Thought network</h2>
                    <div class="mt-4 rounded-3xl border border-white/10 bg-white/[0.03] p-4">
                        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
                            <div class="grid gap-2">
                                <label for="graph-path-from" class="text-xs uppercase tracking-[0.2em] text-stone-400">Start thought</label>
                                <select id="graph-path-from" class="rounded-2xl border border-white/10 bg-stone-950/60 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
                                    <option value="">Select start thought</option>
                                    @foreach ($thoughtOptions as $thoughtOption)
                                        <option value="{{ $thoughtOption->id }}" @selected($selectedFromThought?->id === $thoughtOption->id)>{{ \Illuminate\Support\Str::limit($thoughtOption->content, 42) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid gap-2">
                                <label for="graph-path-to" class="text-xs uppercase tracking-[0.2em] text-stone-400">Target thought</label>
                                <select id="graph-path-to" class="rounded-2xl border border-white/10 bg-stone-950/60 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
                                    <option value="">Select target thought</option>
                                    @foreach ($thoughtOptions as $thoughtOption)
                                        <option value="{{ $thoughtOption->id }}" @selected($selectedToThought?->id === $thoughtOption->id)>{{ \Illuminate\Support\Str::limit($thoughtOption->content, 42) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button id="graph-path-submit" type="button" class="rounded-2xl border border-orange-300/30 bg-orange-300/10 px-4 py-3 text-sm font-semibold text-orange-100 transition hover:bg-orange-300/15">
                                Find Connection
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <label class="text-xs uppercase tracking-[0.2em] text-stone-400">Focus depth</label>
                        <select id="graph-depth" class="rounded-full border border-white/10 bg-stone-950/60 px-3 py-2 text-xs text-stone-100 focus:border-orange-300 focus:outline-none">
                            <option value="1" @selected(! $focusThought)>1-hop</option>
                            <option value="2">2-hop</option>
                        </select>
                        <label class="inline-flex items-center gap-2 text-xs text-stone-300">
                            <input id="graph-backlinks" type="checkbox" class="rounded border-white/20 bg-stone-950 text-orange-300 focus:ring-orange-300" checked>
                            Show backlinks
                        </label>
                        <label class="inline-flex items-center gap-2 text-xs text-stone-300">
                            <input id="graph-syntheses" type="checkbox" class="rounded border-white/20 bg-stone-950 text-orange-300 focus:ring-orange-300" checked>
                            Show syntheses
                        </label>
                    </div>
                </div>
                <div class="relative min-h-[620px] bg-[radial-gradient(circle_at_top,_rgba(251,146,60,0.14),_transparent_30%),linear-gradient(180deg,rgba(12,10,9,0.88),rgba(28,25,23,0.96))]">
                    <div
                        id="graph"
                        data-graph-url="{{ $graphApiUrl }}"
                        data-neighbors-url-template="{{ $neighborsUrlTemplate }}"
                        data-focus-api-url-template="{{ $focusApiUrlTemplate }}"
                        data-focus-route-template="{{ url('/graph/__THOUGHT__') }}"
                        data-initial-focus-thought-id="{{ $focusThought?->id }}"
                        data-space-graph-url="{{ route('graph.index', ['space' => $currentSpace->id]) }}"
                        data-path-api-url="{{ $pathApiUrl }}"
                        data-path-route-url="{{ route('graph.path', ['space' => $currentSpace->id]) }}"
                        data-initial-path-from="{{ $selectedFromThought?->id }}"
                        data-initial-path-to="{{ $selectedToThought?->id }}"
                        class="h-[620px] w-full"
                    ></div>
                </div>
            </section>

            <aside class="glass-panel h-fit p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Graph exploration</p>
                        <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Connected thoughts</h2>
                    </div>
                    <span id="graph-node-count" class="rounded-full border border-white/10 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-stone-300">0 nodes</span>
                </div>

                <p class="mt-4 text-sm leading-6 text-stone-300">Hover a node to lazy-load its neighborhood. Click a node to open the thought in context.</p>
                <p class="mt-3 text-xs uppercase tracking-[0.22em] text-stone-500">Link syntax: <span class="text-orange-200">[[Thought title]]</span></p>

                <div id="graph-selection" class="mt-5 rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Selected thought</p>
                    <p class="mt-3 text-sm leading-6 text-stone-400">Select a node to inspect it and jump back to the board.</p>
                </div>

                <div id="graph-neighbors" class="mt-5 space-y-3"></div>

                <div class="mt-6 rounded-3xl border border-white/10 bg-white/[0.03] p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Path finder</p>
                    <p class="mt-3 text-sm leading-6 text-stone-300">Use the path controls to find the shortest indexed connection between two thoughts, then watch the graph highlight the traversal.</p>
                </div>

                <div class="mt-6 rounded-3xl border border-white/10 bg-white/[0.03] p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Screenshot instructions</p>
                    <p class="mt-3 text-sm leading-6 text-stone-300">Open the graph page, arrange zoom and pan as needed, then use your browser or OS screenshot tool to capture the current graph view.</p>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 pb-12 pt-8 sm:px-6 lg:px-8">
        <div class="glass-panel px-6 py-5">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <a href="{{ $currentSpace ? route('spaces.show', $currentSpace) : route('spaces.index') }}" class="text-xs uppercase tracking-[0.28em] text-stone-400 transition hover:text-orange-200">Back to board</a>
                    <h1 class="mt-3 font-['Space_Grotesk'] text-4xl font-bold text-stone-50">Idea emergence</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-300">Discover recurring tags, strong co-occurrences, and hidden patterns without AI.</p>
                </div>

                <form method="GET" action="{{ route('emergence.index') }}" class="grid gap-3 sm:min-w-[280px]">
                    <label for="space" class="text-xs uppercase tracking-[0.24em] text-stone-500">Space</label>
                    <select id="space" name="space" onchange="this.form.submit()" class="rounded-2xl border border-white/10 bg-stone-950/60 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
                        @foreach ($spaces as $space)
                            <option value="{{ $space->id }}" @selected($currentSpace && $space->is($currentSpace))>{{ $space->title }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-3">
            <section class="glass-panel p-5">
                <p class="text-xs uppercase tracking-[0.26em] text-stone-500">Trending tags</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse ($dashboard['trending_tags'] as $tag)
                        <span class="rounded-full border border-teal-300/20 bg-teal-300/10 px-3 py-1 text-sm text-teal-100">#{{ $tag['tag'] }} · {{ $tag['usage_count'] }}</span>
                    @empty
                        <p class="text-sm text-stone-400">No tags indexed yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="glass-panel p-5">
                <p class="text-xs uppercase tracking-[0.26em] text-stone-500">Thought clusters</p>
                <div class="mt-4 space-y-3">
                    @forelse ($dashboard['thought_clusters'] as $cluster)
                        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-4">
                            <p class="text-sm font-semibold text-stone-100">#{{ $cluster['tag'] }}</p>
                            <p class="mt-2 text-sm text-stone-300">{{ $cluster['usage_count'] }} related thoughts</p>
                        </div>
                    @empty
                        <p class="text-sm text-stone-400">No clusters yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="glass-panel p-5">
                <p class="text-xs uppercase tracking-[0.26em] text-stone-500">Strong co-occurrence</p>
                <div class="mt-4 space-y-3">
                    @forelse ($dashboard['strong_connections'] as $connection)
                        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-4">
                            <p class="text-sm leading-6 text-stone-100">{{ $connection['thought_a']['content'] }}</p>
                            <div class="my-2 h-px bg-white/10"></div>
                            <p class="text-sm leading-6 text-stone-100">{{ $connection['thought_b']['content'] }}</p>
                            <p class="mt-3 text-xs uppercase tracking-[0.22em] text-orange-200">score {{ $connection['score'] }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-stone-400">No strong co-occurrence pairs yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

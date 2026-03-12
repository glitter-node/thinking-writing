<aside class="glass-panel h-fit p-4">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Review your thoughts</p>
            <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Daily review set</h2>
        </div>
        <button x-on:click="loadReviewSuggestions" type="button" class="rounded-full border border-white/10 px-3 py-2 text-xs uppercase tracking-[0.2em] text-stone-300">Refresh</button>
    </div>

    <div x-show="reviewLoading" x-cloak class="mt-4 text-sm text-stone-400">Selecting thoughts to revisit…</div>

    <div x-show="!reviewLoading" x-cloak class="mt-4 space-y-3">
        <template x-for="thought in reviewThoughts" :key="thought.id">
            <div class="rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                <div class="flex items-center justify-between gap-3">
                    <span class="priority-pill" :class="thought.priority === 'high' ? 'bg-rose-400/15 text-rose-200' : (thought.priority === 'medium' ? 'bg-amber-300/15 text-amber-200' : 'bg-teal-300/15 text-teal-200')" x-text="thought.priority"></span>
                    <span class="text-[11px] uppercase tracking-[0.2em] text-stone-500" x-text="thought.stream_title"></span>
                </div>
                <p class="mt-3 text-sm leading-6 text-stone-100" x-text="thought.content"></p>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    <button x-on:click="markReview(thought.id, 'useful')" type="button" class="rounded-2xl border border-white/10 px-3 py-2 text-xs font-semibold text-stone-100 transition hover:border-teal-300/40 hover:text-teal-200">Useful</button>
                    <button x-on:click="markReview(thought.id, 'archive')" type="button" class="rounded-2xl border border-white/10 px-3 py-2 text-xs font-semibold text-stone-100 transition hover:border-stone-300/40 hover:text-stone-200">Archive</button>
                    <button x-on:click="openEvolveThought({ id: thought.id, content: thought.content, priority: thought.priority, tags: [] }); markReview(thought.id, 'evolve')" type="button" class="rounded-2xl border border-orange-300/30 bg-orange-300/10 px-3 py-2 text-xs font-semibold text-orange-100 transition hover:bg-orange-300/15">Evolve</button>
                </div>
            </div>
        </template>

        <div x-show="reviewThoughts.length === 0" x-cloak class="rounded-3xl border border-dashed border-white/10 bg-stone-950/40 p-4 text-sm text-stone-400">
            No review suggestions right now. Capture more thoughts or refresh later.
        </div>
    </div>
</aside>

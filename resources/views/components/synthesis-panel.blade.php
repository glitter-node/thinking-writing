<aside class="glass-panel h-fit p-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Synthesis</p>
            <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Combine ideas</h2>
        </div>
        <span class="rounded-full border border-white/10 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-stone-300" x-text="`${synthesisSuggestions.length} prompts`"></span>
    </div>

    <p class="mt-4 text-sm leading-6 text-stone-300">Select multiple thought cards or start from one of these suggested clusters.</p>

    <div class="mt-4 space-y-3">
        <template x-for="suggestion in synthesisSuggestions" :key="suggestion.label + suggestion.thought_ids.join('-')">
            <div class="rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs uppercase tracking-[0.22em] text-stone-500" x-text="suggestion.label"></p>
                    <button x-on:click="applySynthesisSuggestion(suggestion)" type="button" class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-stone-100 transition hover:border-orange-300/30 hover:text-orange-200">Use</button>
                </div>
                <div class="mt-3 space-y-2">
                    <template x-for="thought in suggestion.thoughts" :key="thought.id">
                        <p class="text-sm leading-6 text-stone-100" x-text="thought.content"></p>
                    </template>
                </div>
            </div>
        </template>
    </div>
</aside>

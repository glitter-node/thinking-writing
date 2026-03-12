<section class="mb-4 rounded-[2rem] border border-teal-300/20 bg-teal-300/5 p-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <p class="text-xs uppercase tracking-[0.28em] text-teal-200/80">Guided prompt</p>
            <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50" x-text="activePrompt?.prompt ?? 'What idea came to you today?'"></h2>
            <p class="mt-2 text-sm leading-6 text-stone-300">Use this to break the empty start problem and get the first thought onto the board.</p>
            <template x-if="suggestedPrompt && suggestedPrompt.prompt !== activePrompt?.prompt">
                <div class="mt-4 rounded-3xl border border-white/10 bg-stone-950/50 p-4">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-stone-500">Suggested from your recent thinking</p>
                    <p class="mt-2 text-sm leading-6 text-stone-100" x-text="suggestedPrompt.prompt"></p>
                    <button x-on:click="useSuggestedPrompt" type="button" class="mt-3 rounded-full border border-orange-300/30 bg-orange-300/10 px-3 py-1 text-xs font-semibold text-orange-100 transition hover:bg-orange-300/15">
                        Use suggestion
                    </button>
                </div>
            </template>
        </div>

        <div class="flex shrink-0 flex-wrap gap-2">
            <template x-if="activePrompt?.category">
                <span class="rounded-full border border-white/10 px-3 py-1 text-[11px] uppercase tracking-[0.22em] text-stone-300" x-text="activePrompt.category"></span>
            </template>
            <button x-on:click="applyPromptToQuickThought" type="button" class="rounded-full border border-white/10 px-3 py-2 text-xs uppercase tracking-[0.2em] text-stone-200">Write from prompt</button>
        </div>
    </div>
</section>

<div x-show="synthesisModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/80 px-4">
    <div @click.outside="synthesisModalOpen = false" class="w-full max-w-2xl rounded-[2rem] border border-white/10 bg-stone-950 p-6 shadow-2xl shadow-black/50">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.28em] text-stone-500">Thought synthesis</p>
                <h2 class="mt-2 font-['Space_Grotesk'] text-3xl font-bold text-stone-50">Combine selected thoughts</h2>
            </div>
            <button x-on:click="synthesisModalOpen = false" type="button" class="rounded-full border border-white/10 p-2 text-stone-300">X</button>
        </div>

        <div class="mt-5 rounded-3xl border border-white/10 bg-white/[0.03] p-4">
            <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Synthesizing from</p>
            <div class="mt-3 space-y-2">
                <template x-for="thought in selectedThoughtSummaries" :key="thought.id">
                    <p class="text-sm leading-6 text-stone-100" x-text="thought.content"></p>
                </template>
            </div>
        </div>

        <div class="mt-5">
            <textarea x-model="synthesisContent" rows="6" class="w-full rounded-3xl border border-white/10 bg-white/5 px-4 py-4 text-sm text-stone-100 focus:border-orange-300 focus:outline-none" placeholder="Write the synthesized thought..."></textarea>
        </div>

        <div class="mt-5 flex items-center justify-between gap-3">
            <span x-show="synthesisError" x-text="synthesisError" x-cloak class="text-sm text-rose-200"></span>
            <button x-bind:disabled="selectedThoughtIds.length < 2 || synthesisSubmitting" x-on:click="submitSynthesis" type="button" class="rounded-2xl border border-orange-300/30 bg-orange-300/10 px-5 py-3 text-sm font-semibold text-orange-100 transition hover:bg-orange-300/15 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-show="!synthesisSubmitting">Save synthesis</span>
                <span x-show="synthesisSubmitting" x-cloak>Saving…</span>
            </button>
        </div>
    </div>
</div>

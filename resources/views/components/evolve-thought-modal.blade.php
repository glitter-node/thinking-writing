<div x-show="evolveModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/80 px-4">
    <div @click.outside="evolveModalOpen = false" class="w-full max-w-lg rounded-[2rem] border border-white/10 bg-stone-950 p-6 shadow-2xl shadow-black/50">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.28em] text-stone-500">Thought evolution</p>
                <h2 class="mt-2 font-['Space_Grotesk'] text-3xl font-bold text-stone-50">Evolve this thought</h2>
            </div>
            <button x-on:click="evolveModalOpen = false" type="button" class="rounded-full border border-white/10 p-2 text-stone-300">X</button>
        </div>

        <template x-if="evolveParentThought">
            <div class="mt-5">
                <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Current thought</p>
                    <p class="mt-2 text-sm leading-6 text-stone-100" x-text="evolveParentThought.content"></p>
                </div>

                <div class="mt-4 grid gap-3">
                    <textarea x-model="evolveContent" rows="4" placeholder="Write the next version of this idea..." class="rounded-2xl border border-white/10 bg-stone-900/70 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none"></textarea>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <select x-model="evolvePriority" class="rounded-2xl border border-white/10 bg-stone-900/70 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                        <input x-model="evolveTags" type="text" placeholder="tags, comma, separated" class="rounded-2xl border border-white/10 bg-stone-900/70 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none">
                    </div>
                    <button x-on:click="submitEvolution" type="button" class="rounded-2xl bg-orange-300 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-200">
                        <span x-show="!evolveSubmitting">Create evolved thought</span>
                        <span x-show="evolveSubmitting" x-cloak>Saving…</span>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

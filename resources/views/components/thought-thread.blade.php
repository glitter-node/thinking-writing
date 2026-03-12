<div x-show="threadModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/80 px-4">
    <div @click.outside="threadModalOpen = false" class="w-full max-w-2xl rounded-[2rem] border border-white/10 bg-stone-950 p-6 shadow-2xl shadow-black/50">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.28em] text-stone-500">Thought thread</p>
                <h2 class="mt-2 font-['Space_Grotesk'] text-3xl font-bold text-stone-50">Evolution chain</h2>
            </div>
            <button x-on:click="threadModalOpen = false" type="button" class="rounded-full border border-white/10 p-2 text-stone-300">X</button>
        </div>

        <div x-show="threadLoading" x-cloak class="mt-5 text-sm text-stone-400">Loading thread…</div>

        <div x-show="!threadLoading" x-cloak class="mt-5 space-y-3">
            <template x-for="(item, index) in threadThoughts" :key="item.id">
                <div>
                    <div class="rounded-3xl border border-white/10 p-4" :class="item.id === threadCurrentThoughtId ? 'bg-orange-300/10 border-orange-300/30' : 'bg-white/5'">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs uppercase tracking-[0.24em] text-stone-500" x-text="item.stream_title"></span>
                            <span class="text-[11px] uppercase tracking-[0.2em] text-stone-500" x-text="item.created_at_human"></span>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-stone-100" x-text="item.content"></p>
                        <button x-on:click="scrollToThought(item.id)" type="button" class="mt-4 rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-stone-100 transition hover:border-orange-300/40 hover:text-orange-200">Jump to card</button>
                    </div>
                    <div x-show="index < threadThoughts.length - 1" class="mx-auto my-2 h-6 w-px bg-white/15"></div>
                </div>
            </template>
        </div>
    </div>
</div>

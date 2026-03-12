<aside class="glass-panel h-fit p-4">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Rediscover</p>
            <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Past sparks</h2>
        </div>
        <button x-on:click="loadRediscover" type="button" class="rounded-full border border-white/10 px-3 py-2 text-xs uppercase tracking-[0.2em] text-stone-300">Refresh</button>
    </div>

    <div x-show="rediscoverLoading" x-cloak class="mt-4 text-sm text-stone-400">Looking through your board…</div>

    <div x-show="!rediscoverLoading" x-cloak class="mt-4 space-y-3">
        <template x-for="entry in rediscoverEntries" :key="entry.label">
            <div class="rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500" x-text="entry.label"></p>
                    <template x-if="entry.thought">
                        <span class="text-[11px] uppercase tracking-[0.2em] text-stone-500" x-text="entry.thought.created_at_human"></span>
                    </template>
                </div>

                <template x-if="entry.thought">
                    <div>
                        <p class="mt-3 text-sm leading-6 text-stone-100" x-text="entry.thought.content"></p>
                        <div class="mt-3 flex items-center justify-between gap-3">
                            <span class="text-xs uppercase tracking-[0.2em] text-stone-400" x-text="entry.thought.stream_title"></span>
                            <a
                                :href="'#thought-' + entry.thought.id"
                                x-on:click.prevent="scrollToThought(entry.thought.id)"
                                class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-stone-100 transition hover:border-orange-300/40 hover:text-orange-200"
                            >
                                Jump to thought
                            </a>
                        </div>
                    </div>
                </template>

                <template x-if="!entry.thought">
                    <p class="mt-3 text-sm text-stone-400">Nothing captured for this window yet.</p>
                </template>
            </div>
        </template>
    </div>
</aside>

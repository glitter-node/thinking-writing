@props(['space', 'firstStream'])

<section class="mb-6 rounded-[2rem] border border-orange-300/20 bg-orange-300/5 p-4">
    <div class="flex items-start gap-4">
        <div class="rounded-2xl bg-orange-300/15 p-3 text-orange-200">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M10.75 2.5a.75.75 0 0 0-1.5 0v6h-6a.75.75 0 0 0 0 1.5h6v6a.75.75 0 0 0 1.5 0v-6h6a.75.75 0 0 0 0-1.5h-6v-6Z" />
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.28em] text-orange-200/80">Quick capture</p>
                    <p class="mt-1 text-sm text-stone-300">Press Enter to save instantly to <span class="font-semibold text-stone-100">{{ $firstStream?->title ?? 'your first stream' }}</span>.</p>
                </div>
                <div class="text-xs uppercase tracking-[0.24em] text-stone-500">Under 3 seconds</div>
            </div>
            <div class="mt-3">
                <input
                    x-model="quickThought"
                    x-on:keydown.enter.prevent="submitQuickThought"
                    type="text"
                    @disabled(! $firstStream)
                    placeholder="{{ $firstStream ? 'Write the thought and press Enter...' : 'Create a stream first to enable quick capture' }}"
                    class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-4 text-base text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                >
            </div>
            <div class="mt-3 flex items-center gap-3 text-sm">
                <span x-show="quickSubmitting" x-cloak class="text-amber-200">Saving…</span>
                <span x-show="quickSuccess" x-cloak class="text-teal-200">Saved to {{ $firstStream?->title ?? 'stream' }}.</span>
                <span x-show="quickError" x-text="quickError" x-cloak class="text-rose-200"></span>
            </div>
        </div>
    </div>
</section>

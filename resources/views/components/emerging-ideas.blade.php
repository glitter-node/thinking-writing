@props(['space', 'suggestions'])

<aside class="glass-panel h-fit p-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Emerging ideas</p>
            <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Possible connections</h2>
        </div>
        <span class="rounded-full border border-white/10 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-stone-300">{{ count($suggestions['related_thoughts'] ?? []) }}</span>
    </div>

    @if (($suggestions['thought'] ?? null) !== null)
        <div class="mt-4 rounded-3xl border border-white/10 bg-stone-950/60 p-4">
            <p class="text-[11px] uppercase tracking-[0.22em] text-stone-500">Starting from</p>
            <p class="mt-2 text-sm leading-6 text-stone-100">{{ $suggestions['thought']['content'] }}</p>
        </div>
    @endif

    <div class="mt-4 space-y-3">
        @forelse ($suggestions['related_thoughts'] ?? [] as $relatedThought)
            <a href="{{ route('spaces.show', $space) }}#thought-{{ $relatedThought['id'] }}" class="block rounded-3xl border border-white/10 bg-white/[0.03] p-4 transition hover:border-orange-300/30">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs uppercase tracking-[0.22em] text-stone-500">{{ $relatedThought['stream_title'] }}</p>
                    <span class="text-[11px] uppercase tracking-[0.2em] text-orange-200">score {{ $relatedThought['score'] }}</span>
                </div>
                <p class="mt-2 text-sm leading-6 text-stone-100">{{ $relatedThought['content'] }}</p>
            </a>
        @empty
            <p class="rounded-3xl border border-dashed border-white/10 bg-white/[0.03] p-4 text-sm text-stone-400">No strong statistical connections yet. Add more tags, links, or syntheses to surface patterns.</p>
        @endforelse
    </div>

    @if (! empty($suggestions['emerging_themes']))
        <div class="mt-4">
            <p class="text-[11px] uppercase tracking-[0.22em] text-stone-500">Emerging themes</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($suggestions['emerging_themes'] as $theme)
                    <span class="rounded-full border border-teal-300/20 bg-teal-300/10 px-3 py-1 text-xs text-teal-100">#{{ $theme['tag'] }} · {{ $theme['thought_count'] }}</span>
                @endforeach
            </div>
        </div>
    @endif
</aside>

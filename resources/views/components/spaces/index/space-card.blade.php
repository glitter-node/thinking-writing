@props(['space'])

<article class="glass-panel overflow-hidden p-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.28em] text-stone-500">Space</p>
            <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">{{ $space->title }}</h2>
        </div>
        <span class="rounded-full border border-white/10 px-3 py-1 text-xs uppercase tracking-[0.22em] text-stone-300">{{ $space->streams_count }} streams</span>
    </div>
    <p class="mt-4 min-h-[3rem] text-sm leading-6 text-stone-300">{{ $space->description ?: 'No description yet.' }}</p>
    <div class="mt-6 flex items-center justify-between">
        <a href="{{ route('spaces.show', $space) }}" class="rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-stone-100 transition hover:bg-white/15">Open board</a>
        <span class="text-xs uppercase tracking-[0.24em] text-stone-500">{{ $space->thoughts_count }} thoughts</span>
    </div>
</article>

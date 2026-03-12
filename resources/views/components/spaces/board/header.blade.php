@props(['space'])

<div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
    <div>
        <a href="{{ route('spaces.index') }}" class="text-xs uppercase tracking-[0.28em] text-stone-400 transition hover:text-orange-200">Back to spaces</a>
        <h1 class="mt-3 font-['Space_Grotesk'] text-4xl font-bold text-stone-50">{{ $space->title }}</h1>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-300">{{ $space->description ?: 'No description yet.' }}</p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button @click="thoughtModalOpen = true" type="button" class="rounded-full bg-orange-300 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-200">
            Add thought
        </button>
        <span class="rounded-full border border-white/10 px-4 py-3 text-xs uppercase tracking-[0.24em] text-stone-300">{{ $space->streams->count() }} streams</span>
    </div>
</div>

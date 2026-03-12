@props(['space', 'search'])

<form method="GET" action="{{ route('spaces.show', $space) }}" class="rounded-3xl border border-white/10 bg-stone-950/40 p-4">
    <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Search thoughts</p>
    <div class="mt-3 flex gap-3">
        <input name="q" type="text" value="{{ $search }}" placeholder="Search content or tags" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none">
        <button type="submit" class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-stone-100 transition hover:border-orange-300/40 hover:text-orange-200">Search</button>
    </div>
</form>

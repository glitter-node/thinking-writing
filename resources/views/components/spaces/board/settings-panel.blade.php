@props(['space'])

<div class="rounded-3xl border border-white/10 bg-stone-950/40 p-4">
    <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Board settings</p>
    <form method="POST" action="{{ route('spaces.update', $space) }}" class="mt-3 grid gap-3">
        @csrf
        @method('PATCH')
        <input name="title" type="text" value="{{ old('title', $space->title) }}" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
        <textarea name="description" rows="4" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">{{ old('description', $space->description) }}</textarea>
        <button type="submit" class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-stone-100 transition hover:border-orange-300/40 hover:text-orange-200">Update space</button>
    </form>
    <form method="POST" action="{{ route('spaces.destroy', $space) }}" class="mt-3">
        @csrf
        @method('DELETE')
        <button type="submit" class="w-full rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm font-semibold text-rose-100 transition hover:bg-rose-300/15">Delete space</button>
    </form>
</div>

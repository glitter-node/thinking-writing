<form method="POST" action="{{ route('spaces.store') }}" class="grid w-full gap-3 rounded-3xl border border-white/10 bg-stone-950/50 p-4 lg:w-[420px]">
    @csrf
    <input name="title" type="text" placeholder="New space title" value="{{ old('title') }}" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none">
    <textarea name="description" rows="2" placeholder="What is this board for?" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none">{{ old('description') }}</textarea>
    <button type="submit" class="rounded-2xl bg-orange-300 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-200">Create space</button>
</form>

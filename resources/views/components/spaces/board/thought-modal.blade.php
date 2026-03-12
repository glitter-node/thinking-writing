@props(['space', 'streamOptions'])

<div x-show="thoughtModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/80 px-4">
    <div @click.outside="thoughtModalOpen = false" class="w-full max-w-xl rounded-[2rem] border border-white/10 bg-stone-950 p-6 shadow-2xl shadow-black/50">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.28em] text-stone-500">Thought modal</p>
                <h2 class="mt-2 font-['Space_Grotesk'] text-3xl font-bold text-stone-50">Add a new thought</h2>
            </div>
            <button @click="thoughtModalOpen = false" type="button" class="rounded-full border border-white/10 p-2 text-stone-300">X</button>
        </div>

        @if ($space->streams->isNotEmpty())
            <div class="mt-5 space-y-4">
                @foreach ($streamOptions as $streamOption)
                    <form method="POST" action="{{ route('streams.thoughts.store', $streamOption['id']) }}" class="grid gap-3 rounded-3xl border border-white/10 bg-white/5 p-4">
                        @csrf
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-stone-100">{{ $streamOption['title'] }}</p>
                            <span class="text-xs uppercase tracking-[0.22em] text-stone-500">Target stream</span>
                        </div>
                        <textarea name="content" rows="3" placeholder="Capture the thought..." class="rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none"></textarea>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <select name="priority" class="rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
                                <option value="medium">Medium priority</option>
                                <option value="high">High priority</option>
                                <option value="low">Low priority</option>
                            </select>
                            <input name="tags" type="text" placeholder="tags, comma, separated" class="rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none">
                        </div>
                        <button type="submit" class="rounded-2xl bg-orange-300 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-200">Save to {{ $streamOption['title'] }}</button>
                    </form>
                @endforeach
            </div>
        @else
            <p class="mt-6 text-sm text-stone-300">Create a stream before adding thoughts.</p>
        @endif
    </div>
</div>

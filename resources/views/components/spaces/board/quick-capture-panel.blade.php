@props(['space'])

<div class="rounded-3xl border border-white/10 bg-stone-950/40 p-4">
    <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Quick capture</p>
    @if ($space->streams->isNotEmpty())
        <div class="mt-3 space-y-3">
            @foreach ($space->streams->take(2) as $captureStream)
                <form method="POST" action="{{ route('streams.thoughts.store', $captureStream) }}" class="grid gap-3 rounded-3xl border border-white/10 bg-white/5 p-3">
                    @csrf
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-stone-100">{{ $captureStream->title }}</span>
                        <span class="text-[11px] uppercase tracking-[0.2em] text-stone-500">Quick lane</span>
                    </div>
                    <textarea name="content" rows="2" placeholder="Drop a thought before it evaporates..." class="rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none"></textarea>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <select name="priority" class="rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
                            <option value="medium">Medium priority</option>
                            <option value="high">High priority</option>
                            <option value="low">Low priority</option>
                        </select>
                        <input name="tags" type="text" placeholder="tags, comma, separated" class="rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none">
                    </div>
                    <button type="submit" class="rounded-2xl bg-teal-300 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-teal-200">Capture to {{ $captureStream->title }}</button>
                </form>
            @endforeach
        </div>
    @else
        <p class="mt-3 text-sm text-stone-300">Create a stream first, then capture thoughts into it.</p>
    @endif
</div>

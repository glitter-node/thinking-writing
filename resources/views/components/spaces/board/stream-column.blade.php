@props(['stream', 'search'])

<div class="glass-panel flex w-[min(20rem,85vw)] min-w-[18rem] shrink-0 flex-col p-4 md:w-[360px]">
    <div x-data="{ editStream: false }" class="mb-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.26em] text-stone-500">Stream</p>
                <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">{{ $stream->title }}</h2>
            </div>
            <div class="flex items-center gap-2">
                <span data-stream-count class="rounded-full border border-white/10 px-3 py-1 text-xs uppercase tracking-[0.22em] text-stone-300">{{ $stream->thoughts->count() }}</span>
                <button @click="editStream = ! editStream" type="button" class="rounded-full border border-white/10 px-3 py-1 text-xs uppercase tracking-[0.22em] text-stone-300">Edit</button>
            </div>
        </div>

        <div x-show="editStream" x-cloak class="mt-4 space-y-3">
            <form method="POST" action="{{ route('streams.update', $stream) }}" class="grid gap-3 rounded-3xl border border-white/10 bg-stone-950/60 p-3">
                @csrf
                @method('PATCH')
                <input name="title" type="text" value="{{ $stream->title }}" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
                <button type="submit" class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-stone-100 transition hover:border-orange-300/40 hover:text-orange-200">Save stream</button>
            </form>
            <form method="POST" action="{{ route('streams.destroy', $stream) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm font-semibold text-rose-100 transition hover:bg-rose-300/15">Delete stream</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('streams.thoughts.store', $stream) }}" class="mb-4 grid gap-3 rounded-3xl border border-white/10 bg-stone-950/60 p-3">
        @csrf
        <textarea name="content" rows="3" placeholder="Quick capture into {{ $stream->title }}" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none"></textarea>
        <div class="grid gap-3 sm:grid-cols-2">
            <select name="priority" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="low">Low</option>
            </select>
            <input name="tags" type="text" placeholder="tags" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none">
        </div>
        <button type="submit" class="rounded-2xl bg-teal-300 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-teal-200">Capture</button>
    </form>

    <div data-thought-list data-stream-id="{{ $stream->id }}" class="flex min-h-[220px] flex-1 flex-col gap-3">
        @forelse ($stream->thoughts as $thought)
            <x-spaces.board.thought-card :thought="$thought" />
        @empty
            <div data-empty-state class="rounded-[1.7rem] border border-dashed border-white/10 bg-white/[0.03] p-6 text-sm text-stone-400">
                @if ($search !== '')
                    No thoughts match "{{ $search }}" in this stream.
                @else
                    No thoughts here yet. Capture one above or drag a thought into this stream.
                @endif
            </div>
        @endforelse
    </div>
</div>

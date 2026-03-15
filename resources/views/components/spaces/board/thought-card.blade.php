@props(['thought'])

<article id="thought-{{ $thought->id }}" data-thought-card data-thought-id="{{ $thought->id }}" data-raw-content="{{ $thought->content }}" data-move-url="{{ route('thoughts.move', $thought) }}" x-data="{ editing: false }" class="rounded-[1.7rem] border border-white/10 bg-stone-950/80 p-4 shadow-lg shadow-black/20">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" data-drag-handle class="inline-flex cursor-grab items-center rounded-full border border-white/10 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-stone-300 active:cursor-grabbing">
                Drag
            </button>
            <span class="priority-pill {{ $thought->priority === 'high' ? 'bg-rose-400/15 text-rose-200' : ($thought->priority === 'medium' ? 'bg-amber-300/15 text-amber-200' : 'bg-teal-300/15 text-teal-200') }}">
                {{ $thought->priority }}
            </span>
            <span class="rounded-full border border-sky-300/20 bg-sky-300/10 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-sky-100">
                {{ $thought->stage ?? 'thought' }}
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
            <label class="inline-flex items-center gap-2 rounded-full border border-white/10 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-stone-300">
                <input type="checkbox" class="rounded border-white/20 bg-stone-950 text-orange-300 focus:ring-orange-300" :checked="isThoughtSelected({{ $thought->id }})" x-on:change="toggleThoughtSelection({ id: {{ $thought->id }}, content: @js($thought->content) })">
                Select
            </label>
            @if ($thought->parent_id)
                <span class="rounded-full border border-orange-300/20 bg-orange-300/10 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-orange-100">evolved</span>
            @endif
            @if ($thought->synthesizedFrom)
                <span class="rounded-full border border-teal-300/20 bg-teal-300/10 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-teal-100">synthesized</span>
            @endif
            <button @click="editing = ! editing" type="button" class="rounded-full border border-white/10 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-stone-300">Edit</button>
        </div>
    </div>

    <p data-thought-content-display class="mt-4 break-words whitespace-pre-line text-sm leading-6 text-stone-100">{{ $thought->content }}</p>

    <div class="mt-4 flex flex-wrap items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-stone-500">
        <span>thought</span>
        <span>&rarr;</span>
        <span>concept</span>
        <span>&rarr;</span>
        <span>project</span>
        <span>&rarr;</span>
        <span>task</span>
        <span>&rarr;</span>
        <span>outcome</span>
    </div>

    @if (! empty($thought->tags))
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($thought->tags as $tag)
                <span class="rounded-full bg-white/5 px-3 py-1 text-xs text-stone-300">#{{ $tag }}</span>
            @endforeach
        </div>
    @endif

    @if ($thought->outgoingLinks->isNotEmpty())
        <div class="mt-4 space-y-2">
            <p class="text-[11px] uppercase tracking-[0.24em] text-stone-500">Linked Thoughts</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($thought->outgoingLinks as $link)
                    @if ($link->targetThought)
                        <button x-on:click="scrollToThought({{ $link->targetThought->id }})" type="button" class="rounded-full border border-teal-300/25 bg-teal-300/10 px-3 py-1 text-xs text-teal-100 transition hover:bg-teal-300/15">
                            {{ \Illuminate\Support\Str::limit($link->targetThought->content, 36) }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if ($thought->incomingLinks->isNotEmpty())
        <div class="mt-4 space-y-2">
            <p class="text-[11px] uppercase tracking-[0.24em] text-stone-500">Referenced By</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($thought->incomingLinks as $link)
                    @if ($link->sourceThought)
                        <button x-on:click="scrollToThought({{ $link->sourceThought->id }})" type="button" class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-stone-200 transition hover:border-orange-300/30 hover:text-orange-100">
                            {{ \Illuminate\Support\Str::limit($link->sourceThought->content, 36) }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if ($thought->synthesizedFrom)
        <div class="mt-4 space-y-2">
            <p class="text-[11px] uppercase tracking-[0.24em] text-stone-500">Synthesized From</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($thought->synthesizedFrom->items as $item)
                    @if ($item->thought)
                        <button x-on:click="scrollToThought({{ $item->thought->id }})" type="button" class="rounded-full border border-orange-300/25 bg-orange-300/10 px-3 py-1 text-xs text-orange-100 transition hover:bg-orange-300/15">
                            {{ \Illuminate\Support\Str::limit($item->thought->content, 36) }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if ($thought->project)
        <div class="mt-4 space-y-2">
            <p class="text-[11px] uppercase tracking-[0.24em] text-stone-500">Project</p>
            <a href="{{ route('projects.index') }}#project-{{ $thought->project->id }}" class="block rounded-3xl border border-white/10 bg-white/[0.03] p-4 transition hover:border-orange-300/30">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-stone-100">{{ $thought->project->title }}</p>
                    <span class="text-[11px] uppercase tracking-[0.2em] text-teal-200">{{ $thought->project->status }}</span>
                </div>
                <p class="mt-2 text-sm text-stone-300">{{ $thought->project->tasks->count() }} tasks</p>
            </a>
        </div>
    @endif

    @if ($thought->versions->isNotEmpty())
        <details class="mt-4 rounded-3xl border border-white/10 bg-white/[0.03] p-4">
            <summary class="cursor-pointer list-none text-[11px] uppercase tracking-[0.24em] text-stone-400">
                Version history
            </summary>
            <div class="mt-4 space-y-3">
                @foreach ($thought->versions->take(5) as $version)
                    <div class="rounded-2xl border border-white/10 bg-stone-950/50 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-orange-100">v{{ $version->version }}</p>
                            <p class="text-[11px] uppercase tracking-[0.18em] text-stone-500">{{ $version->created_at->diffForHumans() }}</p>
                        </div>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-200">{{ $version->content }}</p>
                    </div>
                @endforeach
            </div>
        </details>
    @endif

    @if ($thought->events->isNotEmpty())
        <div class="mt-4 space-y-2">
            <p class="text-[11px] uppercase tracking-[0.24em] text-stone-500">Recent events</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($thought->events->take(4) as $event)
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[11px] uppercase tracking-[0.18em] text-stone-300">
                        {{ $event->event_type }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-4 flex flex-wrap gap-2">
        @if (($thought->stage ?? 'thought') === 'thought')
            <form method="POST" action="{{ route('thoughts.promote', $thought) }}">
                @csrf
                <button type="submit" class="rounded-full border border-sky-300/30 bg-sky-300/10 px-3 py-1 text-xs font-semibold text-sky-100 transition hover:bg-sky-300/15">
                    Promote to Concept
                </button>
            </form>
        @endif
        @if (($thought->stage ?? 'thought') !== 'project' && ! $thought->project)
            <form method="POST" action="{{ route('thoughts.projects.store', $thought) }}">
                @csrf
                <button type="submit" class="rounded-full border border-teal-300/30 bg-teal-300/10 px-3 py-1 text-xs font-semibold text-teal-100 transition hover:bg-teal-300/15">
                    Create Project
                </button>
            </form>
        @endif
        @if ($thought->project && $thought->project->tasks->isEmpty())
            <form method="POST" action="{{ route('projects.tasks.store', $thought->project) }}">
                @csrf
                <button type="submit" class="rounded-full border border-amber-300/30 bg-amber-300/10 px-3 py-1 text-xs font-semibold text-amber-100 transition hover:bg-amber-300/15">
                    Create Tasks
                </button>
            </form>
        @endif
        <button x-on:click="openEvolveThought({ id: {{ $thought->id }}, content: @js($thought->content), priority: @js($thought->priority), tags: @js($thought->tags ?? []) })" type="button" class="rounded-full border border-orange-300/30 bg-orange-300/10 px-3 py-1 text-xs font-semibold text-orange-100 transition hover:bg-orange-300/15">
            Evolve thought
        </button>
        <button x-on:click="openThread({{ $thought->id }})" type="button" class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-stone-100 transition hover:border-teal-300/40 hover:text-teal-200">
            View thread
        </button>
        <a href="{{ route('graph.focus', $thought) }}" class="rounded-full border border-cyan-300/30 bg-cyan-300/10 px-3 py-1 text-xs font-semibold text-cyan-100 transition hover:bg-cyan-300/15">
            Open in Graph
        </a>
        <a href="{{ route('graph.path', ['from' => $thought->id, 'space' => $thought->stream->space_id]) }}" class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-stone-100 transition hover:border-orange-300/30 hover:text-orange-100">
            Find Connection
        </a>
    </div>

    <div x-show="editing" x-cloak class="mt-4 space-y-3 border-t border-white/10 pt-4">
        <form method="POST" action="{{ route('thoughts.update', $thought) }}" class="grid gap-3">
            @csrf
            @method('PATCH')
            <textarea name="content" rows="3" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">{{ $thought->content }}</textarea>
            <div class="grid gap-3 sm:grid-cols-2">
                <select name="priority" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
                    @foreach (['low', 'medium', 'high'] as $priority)
                        <option value="{{ $priority }}" @selected($thought->priority === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
                <input name="tags" type="text" value="{{ implode(', ', $thought->tags ?? []) }}" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
            </div>
            <button type="submit" class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-stone-100 transition hover:border-orange-300/40 hover:text-orange-200">Save thought</button>
        </form>
        <form method="POST" action="{{ route('thoughts.destroy', $thought) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm font-semibold text-rose-100 transition hover:bg-rose-300/15">Archive thought</button>
        </form>
    </div>
</article>

<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 pb-12 pt-8 sm:px-6 lg:px-8">
        <div class="glass-panel px-6 py-5">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <a href="{{ route('spaces.index') }}" class="text-xs uppercase tracking-[0.28em] text-stone-400 transition hover:text-orange-200">Back to spaces</a>
                    <h1 class="mt-3 font-['Space_Grotesk'] text-4xl font-bold text-stone-50">Project board</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-300">Execution artifacts derived from thoughts, grouped in a kanban-style flow.</p>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-teal-300/20 bg-teal-300/10 px-4 py-3 text-sm text-teal-100">{{ session('status') }}</div>
        @endif

        @php
            $columns = [
                'active' => 'Active',
                'complete' => 'Complete',
            ];
        @endphp

        <div class="mt-6">
            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($columns as $status => $label)
                    <section class="glass-panel flex min-w-0 flex-col p-4">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.26em] text-stone-500">Projects</p>
                                <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">{{ $label }}</h2>
                            </div>
                            <span class="rounded-full border border-white/10 px-3 py-1 text-xs uppercase tracking-[0.22em] text-stone-300">{{ $projects->where('status', $status)->count() }}</span>
                        </div>

                        <div class="space-y-4">
                            @forelse ($projects->where('status', $status) as $project)
                                <article id="project-{{ $project->id }}" class="rounded-[1.7rem] border border-white/10 bg-stone-950/80 p-4 shadow-lg shadow-black/20">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.22em] text-stone-500">{{ $project->thought->stage }}</p>
                                            <h3 class="mt-2 text-lg font-semibold text-stone-50">{{ $project->title }}</h3>
                                        </div>
                                        <a href="{{ route('spaces.show', $project->thought->stream->space) }}#thought-{{ $project->thought->id }}" class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-stone-100 transition hover:border-orange-300/30 hover:text-orange-200">Open thought</a>
                                    </div>

                                    <p class="mt-3 text-sm leading-6 text-stone-300">{{ $project->description }}</p>

                                    @if ($project->tasks->isEmpty())
                                        <form method="POST" action="{{ route('projects.tasks.store', $project) }}" class="mt-4">
                                            @csrf
                                            <button type="submit" class="w-full rounded-2xl border border-amber-300/30 bg-amber-300/10 px-4 py-3 text-sm font-semibold text-amber-100 transition hover:bg-amber-300/15">
                                                Create Tasks
                                            </button>
                                        </form>
                                    @else
                                        <div class="mt-4 space-y-3">
                                            @foreach ($project->tasks as $task)
                                                <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-4">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div>
                                                            <p class="text-sm font-semibold text-stone-100">{{ $task->title }}</p>
                                                            <p class="mt-1 text-xs uppercase tracking-[0.22em] text-stone-500">{{ $task->priority }} · {{ $task->status }}</p>
                                                        </div>
                                                        @if ($task->status !== 'done')
                                                            <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="rounded-full border border-teal-300/30 bg-teal-300/10 px-3 py-1 text-xs font-semibold text-teal-100 transition hover:bg-teal-300/15">
                                                                    Complete
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </article>
                            @empty
                                <div class="rounded-[1.7rem] border border-dashed border-white/10 bg-white/[0.03] p-6 text-sm text-stone-400">
                                    No {{ strtolower($label) }} projects yet.
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>

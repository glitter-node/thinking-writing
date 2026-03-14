<x-app-layout>
    <div
        x-data="boardState({
            csrfToken: @js(csrf_token()),
            quickThoughtUrl: @js(route('spaces.quick-thoughts.store', $space)),
            searchUrl: @js(route('spaces.search', $space)),
            rediscoverUrl: @js(route('spaces.rediscover', $space)),
            reviewUrl: @js(route('spaces.reviews', $space)),
            reviewStoreUrlTemplate: @js(url('/thoughts/__THOUGHT__/reviews')),
            threadUrlTemplate: @js(url('/thoughts/__THOUGHT__/thread')),
            evolveUrlTemplate: @js(url('/thoughts/__THOUGHT__/evolve')),
            synthesisStoreUrl: @js(route('spaces.syntheses.store', $space)),
            firstStreamId: @js($space->streams->first()?->id),
            initialSearch: @js($search),
            promptPack: @js($promptPack),
            synthesisSuggestions: @js($synthesisSuggestions),
            streak: @js($streak),
        })"
        class="mx-auto max-w-7xl px-4 pb-12 pt-8 sm:px-6 lg:px-8"
    >
        <div class="glass-panel mb-6 px-6 py-5">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <a href="{{ route('spaces.index') }}" class="text-xs uppercase tracking-[0.28em] text-stone-400 transition hover:text-orange-200">Back to spaces</a>
                    <h1 class="mt-3 font-['Space_Grotesk'] text-4xl font-bold text-stone-50">{{ $space->title }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-300">{{ $space->description ?: 'No description yet.' }}</p>
                </div>
                <div class="grid w-full gap-3 sm:flex sm:w-auto sm:flex-wrap sm:items-center">
                    <a href="{{ route('graph.index', ['space' => $space->id]) }}" class="rounded-full border border-orange-300/30 bg-orange-300/10 px-4 py-3 text-center text-xs uppercase tracking-[0.24em] text-orange-100 transition hover:bg-orange-300/15">Open graph</a>
                    <a href="{{ route('canvas.index', ['space' => $space->id]) }}" class="rounded-full border border-cyan-300/30 bg-cyan-300/10 px-4 py-3 text-center text-xs uppercase tracking-[0.24em] text-cyan-100 transition hover:bg-cyan-300/15">Open canvas</a>
                    <a href="{{ route('emergence.index', ['space' => $space->id]) }}" class="rounded-full border border-teal-300/30 bg-teal-300/10 px-4 py-3 text-center text-xs uppercase tracking-[0.24em] text-teal-100 transition hover:bg-teal-300/15">Open emergence</a>
                    <a href="{{ route('projects.index') }}" class="rounded-full border border-sky-300/30 bg-sky-300/10 px-4 py-3 text-center text-xs uppercase tracking-[0.24em] text-sky-100 transition hover:bg-sky-300/15">Open projects</a>
                    <a href="{{ route('thoughts.export') }}" class="rounded-full border border-white/10 px-4 py-3 text-center text-xs uppercase tracking-[0.24em] text-stone-200 transition hover:border-orange-300/30 hover:text-orange-100">Export JSON</a>
                    <div class="rounded-full border border-white/10 px-4 py-3 text-center text-xs uppercase tracking-[0.24em] text-stone-300">{{ $space->streams->count() }} streams</div>
                </div>
            </div>

            <div class="mt-6">
                <x-search-box />
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-teal-300/20 bg-teal-300/10 px-4 py-3 text-sm text-teal-100">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-300/20 bg-rose-300/10 px-4 py-3 text-sm text-rose-100">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="min-w-0">
                <x-thinking-prompt />
                <x-thought-template-selector />
                <x-thinking-streak />
                <x-thought-multiselect />
                <x-quick-thought :space="$space" :first-stream="$space->streams->first()" />

                <section class="mb-8 rounded-[2rem] border border-dashed border-white/15 bg-white/[0.03] p-5">
                    <form method="POST" action="{{ route('spaces.streams.store', $space) }}" class="flex flex-col gap-3 md:flex-row">
                        @csrf
                        <input name="title" type="text" placeholder="Add stream" class="w-full rounded-2xl border border-white/10 bg-stone-950/60 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none">
                        <button type="submit" class="w-full rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/15 md:w-auto">Add stream</button>
                    </form>
                </section>

                <section class="pb-4 md:overflow-x-auto">
                    <div class="grid gap-5 md:flex md:min-w-max">
                        @forelse ($space->streams as $stream)
                            <x-spaces.board.stream-column :stream="$stream" :search="$search" />
                        @empty
                            <div class="glass-panel w-full p-10 text-center">
                                <h2 class="font-['Space_Grotesk'] text-2xl font-bold text-stone-50">No streams yet</h2>
                                <p class="mt-3 text-sm text-stone-300">Create a stream to start organizing your thoughts.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <x-emerging-ideas :space="$space" :suggestions="$emergenceSuggestions" />
                <x-synthesis-panel />
                <x-review-panel />
                <x-rediscover-panel />
            </div>
        </div>

        <x-evolve-thought-modal />
        <x-synthesis-editor />
        <x-thought-thread />
    </div>
</x-app-layout>

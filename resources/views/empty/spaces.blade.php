<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3">
            <p class="text-xs uppercase tracking-[0.3em] text-stone-400">Workspace</p>
            <h1 class="font-['Space_Grotesk'] text-3xl font-bold text-stone-50">No spaces yet</h1>
            <p class="max-w-2xl text-sm text-stone-300">
                Create your first space to start using the {{ $context ?? 'workspace' }}.
            </p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <section class="glass-panel flex flex-col gap-6 p-10 text-center sm:p-12">
            <div class="space-y-3">
                <h2 class="font-['Space_Grotesk'] text-2xl font-bold text-stone-50">You do not have any spaces yet.</h2>
                <p class="text-sm text-stone-300">
                    Visit your spaces page to create one and begin capturing thoughts, exploring the graph, and arranging ideas on the canvas.
                </p>
            </div>

            <div>
                <a
                    href="{{ route('spaces.index') }}"
                    class="inline-flex items-center justify-center rounded-full bg-orange-400 px-6 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-300"
                >
                    Create your first space
                </a>
            </div>
        </section>
    </div>
</x-app-layout>

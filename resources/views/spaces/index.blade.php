<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-stone-400">Workspace</p>
                <h1 class="font-['Space_Grotesk'] text-3xl font-bold text-stone-50">Your idea spaces</h1>
                <p class="mt-2 max-w-2xl text-sm text-stone-300">Create separate boards for product bets, writing systems, research notes, or anything else that needs a lightweight capture loop.</p>
            </div>

            <x-spaces.index.create-form />
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-teal-300/20 bg-teal-300/10 px-4 py-3 text-sm text-teal-100">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-300/20 bg-rose-300/10 px-4 py-3 text-sm text-rose-100">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($spaces as $space)
                <x-spaces.index.space-card :space="$space" />
            @empty
                <div class="glass-panel col-span-full p-10 text-center">
                    <h2 class="font-['Space_Grotesk'] text-2xl font-bold text-stone-50">No spaces yet</h2>
                    <p class="mt-3 text-sm text-stone-300">Create your first space to start capturing thoughts into streams.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>

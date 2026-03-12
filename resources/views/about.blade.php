<x-guest-layout>
    <div class="space-y-6 text-center">
        <div>
            <a href="{{ route('welcome') }}" class="text-xs uppercase tracking-[0.24em] text-stone-500 transition hover:text-orange-200">Back home</a>
            <h1 class="mt-4 font-['Space_Grotesk'] text-3xl font-bold text-stone-100">About Glitter Thought Write</h1>
            <p class="mt-3 text-sm leading-6 text-stone-300">
                A personal thinking workspace for capturing and evolving ideas.
            </p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6">
            <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Application version</p>
            <p class="mt-3 font-['Space_Grotesk'] text-2xl font-bold text-orange-200">{{ app_version() }}</p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 text-left">
            <h2 class="font-['Space_Grotesk'] text-xl font-bold text-stone-100">Versioning strategy</h2>
            <p class="mt-3 text-sm leading-6 text-stone-300">
                Glitter Thought Write follows semantic versioning through Git tags such as <code>v0.1.0</code>, <code>v0.2.0</code>, and <code>v1.0.0</code>.
            </p>
        </div>
    </div>
</x-guest-layout>

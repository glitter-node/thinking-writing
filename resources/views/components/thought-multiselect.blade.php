<section x-show="selectedThoughtIds.length > 0" x-cloak class="mb-4 rounded-[2rem] border border-white/10 bg-white/[0.03] p-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.28em] text-stone-500">Thought multiselect</p>
            <p class="mt-1 text-sm text-stone-300">
                <span x-text="selectedThoughtIds.length"></span> thought<span x-show="selectedThoughtIds.length !== 1">s</span> selected for synthesis.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button x-on:click="clearThoughtSelection" type="button" class="rounded-full border border-white/10 px-3 py-2 text-xs uppercase tracking-[0.2em] text-stone-200">Clear</button>
            <button x-bind:disabled="selectedThoughtIds.length < 2" x-on:click="openSynthesisEditor" type="button" class="rounded-full border border-orange-300/30 bg-orange-300/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-orange-100 disabled:cursor-not-allowed disabled:opacity-50">
                Synthesize
            </button>
        </div>
    </div>
</section>

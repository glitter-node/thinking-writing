<div class="rounded-3xl border border-white/10 bg-stone-950/40 p-4">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Search thoughts</p>
            <p class="mt-1 text-sm text-stone-300">Live filter across the current space with debounced matching and highlighted text.</p>
        </div>
        <div class="flex w-full max-w-xl flex-col gap-3 sm:flex-row sm:items-center">
            <input
                x-model="searchQuery"
                x-on:input="onSearchInput"
                type="text"
                placeholder="Search thought content..."
                class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-100 placeholder:text-stone-500 focus:border-orange-300 focus:outline-none"
            >
            <button x-show="searchQuery.trim() !== ''" x-cloak x-on:click="searchQuery = ''; clearSearch()" type="button" class="w-full rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-stone-100 transition hover:border-orange-300/40 hover:text-orange-200 sm:w-auto">
                Clear
            </button>
        </div>
    </div>
    <p x-show="searchPending" x-cloak class="mt-3 text-sm text-orange-200">Filtering thoughts…</p>
</div>

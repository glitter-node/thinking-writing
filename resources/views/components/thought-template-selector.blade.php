<section class="mb-4 rounded-[2rem] border border-white/10 bg-white/[0.03] p-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.28em] text-stone-500">Thought templates</p>
            <p class="mt-1 text-sm text-stone-300">Pick a starting shape and prefill the quick thought box.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <template x-for="template in templates" :key="template.label">
                <button
                    x-on:click="applyTemplate(template)"
                    type="button"
                    class="rounded-full border border-white/10 bg-stone-950/60 px-4 py-2 text-xs font-semibold text-stone-100 transition hover:border-orange-300/30 hover:text-orange-200"
                    x-text="template.label"
                ></button>
            </template>
        </div>
    </div>
</section>

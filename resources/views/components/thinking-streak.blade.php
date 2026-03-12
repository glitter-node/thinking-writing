<section class="mb-4 rounded-[2rem] border border-orange-300/20 bg-orange-300/5 p-4">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.28em] text-orange-200/80">Thinking streak</p>
            <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50" x-text="streak.label"></h2>
        </div>
        <div class="rounded-full border border-white/10 px-4 py-3 text-xs uppercase tracking-[0.24em] text-stone-300">
            <span x-text="streak.days"></span> day<span x-show="streak.days !== 1">s</span>
        </div>
    </div>
</section>

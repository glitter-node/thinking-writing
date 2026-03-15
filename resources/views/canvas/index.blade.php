<x-app-layout>
    <div
        x-data="thoughtCanvas({
            spaceId: @js($currentSpace->id),
            canvasUrl: @js(route('spaces.canvas', $currentSpace)),
            positionUrlTemplate: @js(url('/thoughts/__THOUGHT__/position')),
            initialCanvas: @js($initialCanvas),
        })"
        x-on:mousemove.window="drag($event)"
        x-on:mouseup.window="stopDrag()"
        class="mx-auto max-w-7xl px-4 pb-12 pt-8 sm:px-6 lg:px-8"
    >
        <div class="glass-panel px-6 py-5">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <a href="{{ route('spaces.show', $currentSpace) }}" class="text-xs uppercase tracking-[0.28em] text-stone-400 transition hover:text-orange-200">Back to board</a>
                    <h1 class="mt-3 font-['Space_Grotesk'] text-4xl font-bold text-stone-50">Spatial thinking canvas</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-300">Position thoughts freely, drag clusters together, and inspect links, evolution, and synthesis in one spatial workspace.</p>
                </div>

                <div class="flex w-full flex-col gap-4 sm:w-auto sm:min-w-[320px]">
                    <form method="GET" action="{{ route('canvas.index') }}" class="grid gap-3">
                        <label for="space" class="text-xs uppercase tracking-[0.24em] text-stone-500">Space</label>
                        <select id="space" name="space" onchange="this.form.submit()" class="rounded-2xl border border-white/10 bg-stone-950/60 px-4 py-3 text-sm text-stone-100 focus:border-orange-300 focus:outline-none">
                            @foreach ($spaces as $space)
                                <option value="{{ $space->id }}" @selected($space->is($currentSpace))>{{ $space->title }}</option>
                            @endforeach
                        </select>
                    </form>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" x-on:click="zoomIn()" class="rounded-full border border-white/10 px-3 py-2 text-xs uppercase tracking-[0.2em] text-stone-200">Zoom in</button>
                        <button type="button" x-on:click="zoomOut()" class="rounded-full border border-white/10 px-3 py-2 text-xs uppercase tracking-[0.2em] text-stone-200">Zoom out</button>
                        <button type="button" x-on:click="reloadViewport()" class="rounded-full border border-orange-300/30 bg-orange-300/10 px-3 py-2 text-xs uppercase tracking-[0.2em] text-orange-100">Refresh viewport</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <section class="glass-panel overflow-hidden p-0">
                <div class="flex flex-col gap-4 border-b border-white/10 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-[0.26em] text-stone-500">Canvas mode</p>
                        <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Freeform thought layout</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" x-on:click="pan('left')" class="rounded-full border border-white/10 px-3 py-1 text-xs text-stone-200">Left</button>
                        <button type="button" x-on:click="pan('right')" class="rounded-full border border-white/10 px-3 py-1 text-xs text-stone-200">Right</button>
                        <button type="button" x-on:click="pan('up')" class="rounded-full border border-white/10 px-3 py-1 text-xs text-stone-200">Up</button>
                        <button type="button" x-on:click="pan('down')" class="rounded-full border border-white/10 px-3 py-1 text-xs text-stone-200">Down</button>
                    </div>
                </div>

                <div x-ref="surface" class="relative min-h-[520px] overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.12),_transparent_30%),radial-gradient(circle_at_bottom,_rgba(251,146,60,0.14),_transparent_26%),linear-gradient(180deg,rgba(12,10,9,0.88),rgba(28,25,23,0.98))] sm:min-h-[640px] lg:min-h-[760px]">
                    <div x-ref="viewport" class="absolute inset-0 origin-top-left transition-transform duration-150 ease-out">
                        <svg class="absolute inset-0 h-full w-full">
                            <template x-for="edge in edges" :key="edge.id">
                                <path
                                    :d="edgePath(edge)"
                                    fill="none"
                                    :stroke="edgeColor(edge)"
                                    stroke-opacity="0.72"
                                    stroke-width="2"
                                    stroke-dasharray="edge.type === 'evolution' ? '8 5' : null"
                                ></path>
                            </template>
                        </svg>

                        <template x-for="node in nodes.filter((item) => item.kind === 'thought')" :key="node.id">
                            <article
                                class="absolute w-48 max-w-[calc(100vw-5rem)] cursor-move rounded-3xl border bg-stone-950/90 p-4 shadow-[0_24px_60px_rgba(0,0,0,0.35)] transition sm:w-56 sm:max-w-none"
                                :class="selectedNodeIds.includes(node.resource_id) ? 'border-orange-300/60 ring-2 ring-orange-300/25' : 'border-white/10'"
                                :style="`left:${node.x}px; top:${node.y}px;`"
                                x-on:mousedown.prevent="startDrag(node, $event)"
                                x-on:click.shift.prevent="toggleSelection(node.resource_id)"
                            >
                                <p class="text-[11px] uppercase tracking-[0.24em] text-stone-500" x-text="node.stream_title"></p>
                                <p class="mt-3 break-words text-sm leading-6 text-stone-100" x-text="node.content"></p>
                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <span class="rounded-full border border-white/10 px-2 py-1 text-[10px] uppercase tracking-[0.2em] text-stone-300">Thought</span>
                                    <a :href="node.href" class="text-xs font-semibold text-orange-200">Open</a>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>
            </section>

            <aside class="glass-panel h-fit p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Spatial controls</p>
                        <h2 class="mt-2 font-['Space_Grotesk'] text-2xl font-bold text-stone-50">Clusters and links</h2>
                    </div>
                    <span class="rounded-full border border-white/10 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-stone-300" x-text="`${thoughtNodes.length} thoughts`"></span>
                </div>

                <p class="mt-4 text-sm leading-6 text-stone-300">Shift-click to multi-select thoughts. Drag a selected set to move a cluster together, then save positions automatically on mouse release.</p>

                <button type="button" x-on:click="createCluster()" class="mt-4 inline-flex rounded-full border border-orange-300/30 bg-orange-300/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-orange-100">
                    Create cluster from selection
                </button>

                <div class="mt-6">
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Suggested clusters</p>
                    <div class="mt-3 space-y-3">
                        <template x-for="cluster in clusterSuggestions" :key="cluster.label">
                            <button type="button" x-on:click="applyCluster(cluster)" class="block w-full rounded-3xl border border-white/10 bg-white/[0.03] p-4 text-left transition hover:border-orange-300/30">
                                <p class="text-sm font-semibold text-stone-100" x-text="cluster.label"></p>
                                <p class="mt-2 text-xs uppercase tracking-[0.22em] text-stone-500" x-text="`${cluster.thought_ids.length} thoughts`"></p>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Custom clusters</p>
                    <div class="mt-3 space-y-3">
                        <template x-for="cluster in userClusters" :key="cluster.label">
                            <button type="button" x-on:click="applyCluster(cluster)" class="block w-full rounded-3xl border border-cyan-400/20 bg-cyan-400/5 p-4 text-left transition hover:border-cyan-300/35">
                                <p class="text-sm font-semibold text-stone-100" x-text="cluster.label"></p>
                                <p class="mt-2 text-xs uppercase tracking-[0.22em] text-stone-500" x-text="`${cluster.thought_ids.length} thoughts`"></p>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="mt-6 rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Edge legend</p>
                    <div class="mt-4 space-y-2 text-sm text-stone-200">
                        <p><span class="mr-2 inline-block h-2.5 w-2.5 rounded-full bg-orange-400"></span>Links</p>
                        <p><span class="mr-2 inline-block h-2.5 w-2.5 rounded-full bg-sky-400"></span>Evolution</p>
                        <p><span class="mr-2 inline-block h-2.5 w-2.5 rounded-full bg-emerald-400"></span>Synthesis</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>

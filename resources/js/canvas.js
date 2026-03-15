import Alpine from 'alpinejs';

const COLORS = {
    link: '#fb923c',
    evolution: '#38bdf8',
    synthesis: '#34d399',
    project: '#c084fc',
    task: '#facc15',
};

Alpine.data('thoughtCanvas', (config) => ({
    spaceId: config.spaceId,
    canvasUrl: config.canvasUrl,
    positionUrlTemplate: config.positionUrlTemplate,
    viewport: config.initialCanvas.viewport,
    nodes: config.initialCanvas.nodes,
    edges: config.initialCanvas.edges,
    clusterSuggestions: config.initialCanvas.clusters,
    selectedNodeIds: [],
    userClusters: [],
    draggingNodeId: null,
    dragStart: null,
    panX: 0,
    panY: 0,
    zoom: 1,
    nodePadding: 16,
    init() {
        this.$nextTick(() => {
            this.render();
        });
    },
    get thoughtNodes() {
        return this.nodes.filter((node) => node.kind === 'thought');
    },
    get positionedThoughts() {
        return this.thoughtNodes.map((node) => ({
            ...node,
            x: Number(node.x ?? 0),
            y: Number(node.y ?? 0),
        }));
    },
    async reloadViewport() {
        const params = new URLSearchParams({
            x: String(Math.round(-this.panX / this.zoom)),
            y: String(Math.round(-this.panY / this.zoom)),
            width: String(Math.round((this.$refs.surface?.clientWidth ?? 1400) / this.zoom)),
            height: String(Math.round((this.$refs.surface?.clientHeight ?? 840) / this.zoom)),
        });
        const response = await fetch(`${this.canvasUrl}?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        const payload = await response.json();
        this.viewport = payload.viewport;
        this.nodes = payload.nodes;
        this.edges = payload.edges;
        this.clusterSuggestions = payload.clusters;
        this.render();
    },
    edgeColor(edge) {
        return COLORS[edge.type] ?? 'rgba(255,255,255,0.2)';
    },
    edgePath(edge) {
        const source = this.nodes.find((node) => node.id === edge.source);
        const target = this.nodes.find((node) => node.id === edge.target);

        if (!source || !target || source.kind !== 'thought' || target.kind !== 'thought') {
            return '';
        }

        return `M ${source.x} ${source.y} L ${target.x} ${target.y}`;
    },
    nodeWidth() {
        const surfaceWidth = this.$refs.surface?.clientWidth ?? 1024;

        return surfaceWidth < 640 ? 192 : 224;
    },
    clampPosition(x, y) {
        const surfaceWidth = this.$refs.surface?.clientWidth ?? 1400;
        const surfaceHeight = this.$refs.surface?.clientHeight ?? 840;
        const maxX = Math.max(this.nodePadding, surfaceWidth - this.nodeWidth() - this.nodePadding);
        const maxY = Math.max(this.nodePadding, surfaceHeight - 120 - this.nodePadding);

        return {
            x: Math.max(this.nodePadding, Math.min(Math.round(x), maxX)),
            y: Math.max(this.nodePadding, Math.min(Math.round(y), maxY)),
        };
    },
    startDrag(node, event) {
        if (node.kind !== 'thought') {
            return;
        }

        if (!this.selectedNodeIds.includes(node.resource_id)) {
            this.selectedNodeIds = [node.resource_id];
        }

        this.draggingNodeId = node.resource_id;
        this.dragStart = {
            pointerX: event.clientX,
            pointerY: event.clientY,
            positions: this.selectedNodeIds.map((thoughtId) => {
                const thought = this.nodes.find((item) => item.resource_id === thoughtId && item.kind === 'thought');

                return {
                    thoughtId,
                    x: thought.x,
                    y: thought.y,
                };
            }),
        };
    },
    drag(event) {
        if (!this.dragStart) {
            return;
        }

        const deltaX = (event.clientX - this.dragStart.pointerX) / this.zoom;
        const deltaY = (event.clientY - this.dragStart.pointerY) / this.zoom;

        this.dragStart.positions.forEach((position) => {
            const thought = this.nodes.find((item) => item.resource_id === position.thoughtId && item.kind === 'thought');

            if (thought) {
                const clamped = this.clampPosition(position.x + deltaX, position.y + deltaY);
                thought.x = clamped.x;
                thought.y = clamped.y;
            }
        });

        this.render();
    },
    async stopDrag() {
        if (!this.dragStart) {
            return;
        }

        const requests = this.selectedNodeIds.map(async (thoughtId) => {
            const thought = this.nodes.find((item) => item.resource_id === thoughtId && item.kind === 'thought');

            if (!thought) {
                return;
            }

            await fetch(this.positionUrlTemplate.replace('__THOUGHT__', thought.resource_id), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({
                    x: thought.x,
                    y: thought.y,
                }),
            });
        });

        await Promise.all(requests);
        this.dragStart = null;
        this.draggingNodeId = null;
    },
    toggleSelection(thoughtId) {
        this.selectedNodeIds = this.selectedNodeIds.includes(thoughtId)
            ? this.selectedNodeIds.filter((id) => id !== thoughtId)
            : [...this.selectedNodeIds, thoughtId];
    },
    createCluster() {
        if (this.selectedNodeIds.length < 2) {
            return;
        }

        this.userClusters.push({
            label: `Cluster ${this.userClusters.length + 1}`,
            thought_ids: [...this.selectedNodeIds],
        });
    },
    applyCluster(cluster) {
        this.selectedNodeIds = [...cluster.thought_ids];
    },
    zoomIn() {
        this.zoom = Math.min(2.4, Number((this.zoom + 0.2).toFixed(2)));
    },
    zoomOut() {
        this.zoom = Math.max(0.5, Number((this.zoom - 0.2).toFixed(2)));
    },
    pan(direction) {
        const step = 120;

        if (direction === 'left') this.panX += step;
        if (direction === 'right') this.panX -= step;
        if (direction === 'up') this.panY += step;
        if (direction === 'down') this.panY -= step;
    },
    render() {
        if (this.$refs.viewport) {
            this.$refs.viewport.style.transform = `translate(${this.panX}px, ${this.panY}px) scale(${this.zoom})`;
        }
    },
}));

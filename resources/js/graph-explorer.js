import cytoscape from 'cytoscape';

const baseNodeSize = (degree, isCenter) => {
    const base = 34 + Math.min(Number(degree || 0) * 6, 30);

    return isCenter ? base + 12 : base;
};

const edgeColor = (type) => {
    if (type === 'evolution') return '#38bdf8';
    if (type === 'synthesis') return '#34d399';
    return '#fb923c';
};

const wait = (ms) => new Promise((resolve) => {
    window.setTimeout(resolve, ms);
});

const createGraph = async () => {
    const container = document.getElementById('graph');

    if (!container) {
        return;
    }

    const graphUrl = container.dataset.graphUrl;
    const neighborsUrlTemplate = container.dataset.neighborsUrlTemplate;
    const focusApiUrlTemplate = container.dataset.focusApiUrlTemplate;
    const focusRouteTemplate = container.dataset.focusRouteTemplate;
    const spaceGraphUrl = container.dataset.spaceGraphUrl;
    const pathApiUrl = container.dataset.pathApiUrl;
    const pathRouteUrl = container.dataset.pathRouteUrl;
    const initialFocusThoughtId = container.dataset.initialFocusThoughtId;
    const initialPathFrom = container.dataset.initialPathFrom;
    const initialPathTo = container.dataset.initialPathTo;
    const detailPanel = document.getElementById('graph-selection');
    const neighborsPanel = document.getElementById('graph-neighbors');
    const countBadge = document.getElementById('graph-node-count');
    const depthSelect = document.getElementById('graph-depth');
    const backlinksToggle = document.getElementById('graph-backlinks');
    const synthesesToggle = document.getElementById('graph-syntheses');
    const pathFromSelect = document.getElementById('graph-path-from');
    const pathToSelect = document.getElementById('graph-path-to');
    const pathButton = document.getElementById('graph-path-submit');

    let isFocused = false;

    const cy = cytoscape({
        container,
        elements: [],
        layout: {
            name: 'cose',
            animate: false,
            padding: 24,
        },
        style: [
            {
                selector: 'node',
                style: {
                    label: 'data(label)',
                    'background-color': '#0f766e',
                    color: '#fafaf9',
                    'text-wrap': 'wrap',
                    'text-max-width': 90,
                    'font-size': 10,
                    'text-valign': 'center',
                    'text-halign': 'center',
                    width: (ele) => baseNodeSize(ele.data('degree'), ele.data('isCenter')),
                    height: (ele) => baseNodeSize(ele.data('degree'), ele.data('isCenter')),
                    'border-width': 2,
                    'border-color': (ele) => (ele.data('isCenter') ? '#fdba74' : '#99f6e4'),
                },
            },
            {
                selector: 'node[isCenter]',
                style: {
                    'background-color': '#fb923c',
                    'border-color': '#ffedd5',
                },
            },
            {
                selector: 'edge',
                style: {
                    width: 2,
                    'line-color': (ele) => edgeColor(ele.data('type')),
                    'target-arrow-color': (ele) => edgeColor(ele.data('type')),
                    'target-arrow-shape': 'triangle',
                    'curve-style': 'bezier',
                    opacity: 0.85,
                },
            },
            {
                selector: '.path-node',
                style: {
                    'background-color': '#fb923c',
                    'border-color': '#ffedd5',
                },
            },
            {
                selector: '.path-edge',
                style: {
                    width: 4,
                    'line-color': '#fb923c',
                    'target-arrow-color': '#fb923c',
                },
            },
            {
                selector: '.path-active',
                style: {
                    'overlay-color': '#fb923c',
                    'overlay-opacity': 0.24,
                    'overlay-padding': 8,
                },
            },
            {
                selector: ':selected',
                style: {
                    'background-color': '#fb923c',
                    'border-color': '#ffedd5',
                    'line-color': '#fdba74',
                    'target-arrow-color': '#fdba74',
                },
            },
        ],
    });

    const updateCount = () => {
        if (countBadge) {
            countBadge.textContent = `${cy.nodes().length} nodes`;
        }
    };

    const clearPathHighlight = () => {
        cy.elements().removeClass('path-node path-edge path-active');
    };

    const renderNeighbors = (nodes = []) => {
        if (!neighborsPanel) {
            return;
        }

        neighborsPanel.replaceChildren();

        if (nodes.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'text-sm leading-6 text-stone-400';
            empty.textContent = isFocused
                ? 'Focused graph loaded. Click a node to recenter the graph.'
                : 'Hover a node to lazy-load its local neighborhood.';
            neighborsPanel.appendChild(empty);
            return;
        }

        nodes.forEach((node) => {
            const link = document.createElement('a');
            link.href = node.data.href;
            link.className = 'block rounded-3xl border border-white/10 bg-white/[0.03] p-4 transition hover:border-orange-300/30';
            link.innerHTML = `<p class="text-sm leading-6 text-stone-100">${node.data.content}</p>`;
            neighborsPanel.appendChild(link);
        });
    };

    const renderSelection = (node) => {
        if (!detailPanel) {
            return;
        }

        detailPanel.innerHTML = `
            <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Selected thought</p>
            <p class="mt-3 text-sm leading-6 text-stone-100">${node.data('content') ?? node.data('label')}</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="${node.data('href')}" class="inline-flex rounded-full border border-orange-300/30 bg-orange-300/10 px-3 py-1 text-xs font-semibold text-orange-100 transition hover:bg-orange-300/15">
                    Open thought
                </a>
                <button type="button" data-focus-thought="${node.id()}" class="inline-flex rounded-full border border-cyan-300/30 bg-cyan-300/10 px-3 py-1 text-xs font-semibold text-cyan-100 transition hover:bg-cyan-300/15">
                    Refocus graph
                </button>
            </div>
        `;

        detailPanel.querySelector('[data-focus-thought]')?.addEventListener('click', async () => {
            await loadFocusedGraph(node.id());
        });
    };

    const applyGraphData = (payload, focused = false) => {
        isFocused = focused;
        clearPathHighlight();
        cy.elements().remove();
        cy.add([...(payload.nodes ?? []), ...(payload.edges ?? [])]);
        cy.layout({
            name: 'cose',
            animate: false,
            padding: 24,
        }).run();
        updateCount();
        renderNeighbors(payload.nodes ?? []);

        if (focused && payload.center) {
            const centerNode = cy.getElementById(String(payload.center.id));

            if (centerNode.length > 0) {
                cy.center(centerNode);
                centerNode.select();
                renderSelection(centerNode);
            }
        }
    };

    const highlightPath = async (path, edges = []) => {
        clearPathHighlight();

        path.forEach((nodeId) => {
            cy.getElementById(String(nodeId)).addClass('path-node');
        });

        edges.forEach((edge) => {
            cy.getElementById(String(edge.data.id)).addClass('path-edge');
        });

        for (let index = 0; index < path.length; index += 1) {
            const node = cy.getElementById(String(path[index]));
            node.addClass('path-active');

            if (index < path.length - 1) {
                const edge = edges.find((item) => item.data.source === String(path[index]) && item.data.target === String(path[index + 1]));

                if (edge) {
                    cy.getElementById(String(edge.data.id)).addClass('path-active');
                }
            }

            // eslint-disable-next-line no-await-in-loop
            await wait(140);
        }
    };

    const loadBaseGraph = async () => {
        const response = await fetch(graphUrl, {
            headers: { Accept: 'application/json' },
        });
        const graphData = await response.json();
        applyGraphData(graphData, false);
        history.replaceState({}, '', spaceGraphUrl);
    };

    const loadFocusedGraph = async (thoughtId) => {
        const params = new URLSearchParams({
            depth: depthSelect?.value ?? '1',
            backlinks: backlinksToggle?.checked ? '1' : '0',
            syntheses: synthesesToggle?.checked ? '1' : '0',
        });
        const response = await fetch(
            `${focusApiUrlTemplate.replace('__THOUGHT__', thoughtId)}?${params.toString()}`,
            { headers: { Accept: 'application/json' } },
        );
        const focusedGraph = await response.json();
        applyGraphData(focusedGraph, true);
        history.replaceState({}, '', focusRouteTemplate.replace('__THOUGHT__', thoughtId));
    };

    const loadPath = async () => {
        const from = pathFromSelect?.value;
        const to = pathToSelect?.value;

        if (!from || !to) {
            return;
        }

        const params = new URLSearchParams({ from, to });
        const response = await fetch(`${pathApiUrl}?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        const pathData = await response.json();

        applyGraphData({ nodes: pathData.nodes, edges: pathData.edges }, false);
        renderNeighbors(pathData.nodes ?? []);
        history.replaceState({}, '', `${pathRouteUrl}&from=${from}&to=${to}`);

        if (pathData.path?.length) {
            await highlightPath(pathData.path, pathData.edges ?? []);
        }
    };

    const loadedNeighbors = new Set();

    cy.on('mouseover', 'node', async (event) => {
        if (isFocused) {
            return;
        }

        const node = event.target;
        const thoughtId = node.id();

        if (loadedNeighbors.has(thoughtId)) {
            return;
        }

        loadedNeighbors.add(thoughtId);

        const neighborResponse = await fetch(
            neighborsUrlTemplate.replace('__THOUGHT__', thoughtId),
            { headers: { Accept: 'application/json' } },
        );
        const neighborData = await neighborResponse.json();

        const newNodes = (neighborData.nodes ?? []).filter((item) => cy.getElementById(item.data.id).length === 0);
        const newEdges = (neighborData.edges ?? []).filter((item) => cy.getElementById(item.data.id).length === 0);

        if (newNodes.length || newEdges.length) {
            cy.add([...newNodes, ...newEdges]);
            cy.layout({
                name: 'cose',
                animate: true,
                animationDuration: 250,
                fit: false,
                padding: 20,
            }).run();
            updateCount();
        }

        renderNeighbors(neighborData.nodes ?? []);
    });

    cy.on('tap', 'node', async (event) => {
        const node = event.target;
        renderSelection(node);
        await loadFocusedGraph(node.id());
    });

    depthSelect?.addEventListener('change', async () => {
        if (isFocused) {
            const center = cy.nodes().filter((node) => node.data('isCenter')).first();

            if (center?.id()) {
                await loadFocusedGraph(center.id());
            }
        }
    });

    backlinksToggle?.addEventListener('change', async () => {
        if (isFocused) {
            const center = cy.nodes().filter((node) => node.data('isCenter')).first();

            if (center?.id()) {
                await loadFocusedGraph(center.id());
            }
        }
    });

    synthesesToggle?.addEventListener('change', async () => {
        if (isFocused) {
            const center = cy.nodes().filter((node) => node.data('isCenter')).first();

            if (center?.id()) {
                await loadFocusedGraph(center.id());
            }
        }
    });

    pathButton?.addEventListener('click', async () => {
        await loadPath();
    });

    if (initialPathFrom && initialPathTo) {
        if (pathFromSelect) {
            pathFromSelect.value = initialPathFrom;
        }

        if (pathToSelect) {
            pathToSelect.value = initialPathTo;
        }

        await loadPath();
    } else if (initialFocusThoughtId) {
        await loadFocusedGraph(initialFocusThoughtId);
    } else {
        await loadBaseGraph();
    }
};

createGraph();

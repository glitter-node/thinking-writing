import Alpine from 'alpinejs';
import Sortable from 'sortablejs';

const debounce = (callback, wait = 300) => {
    let timeoutId;

    return (...args) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => callback(...args), wait);
    };
};

const escapeHtml = (value) => {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
};

const restoreThoughtCardContent = (card) => {
    const contentNode = card.querySelector('[data-thought-content-display]');
    const rawContent = card.dataset.rawContent ?? '';

    if (contentNode) {
        contentNode.textContent = rawContent;
    }
};

const mountSortable = (element) => {
    if (!element || element.dataset.sortableInitialized === 'true') {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    element.dataset.sortableInitialized = 'true';

    Sortable.create(element, {
        group: 'glitter-thoughts',
        animation: 150,
        ghostClass: 'thought-ghost',
        dragClass: 'thought-dragging',
        onEnd: async (event) => {
            const item = event.item;
            const moveUrl = item.dataset.moveUrl;
            const streamId = event.to.dataset.streamId;
            const position = event.newIndex + 1;

            if (!moveUrl || !streamId || !csrfToken) {
                window.location.reload();
                return;
            }

            const response = await fetch(moveUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    stream_id: Number(streamId),
                    position,
                }),
            });

            if (!response.ok) {
                window.location.reload();
            }
        },
    });
};

Alpine.data('boardState', (config) => ({
    quickThought: '',
    quickSubmitting: false,
    quickSuccess: false,
    quickError: '',
    activePrompt: config.promptPack?.daily ?? null,
    suggestedPrompt: config.promptPack?.suggested ?? null,
    templates: config.promptPack?.templates ?? [],
    searchQuery: '',
    searchPending: false,
    rediscoverEntries: [],
    rediscoverLoading: true,
    reviewThoughts: [],
    reviewLoading: true,
    synthesisSuggestions: config.synthesisSuggestions ?? [],
    selectedThoughtIds: [],
    selectedThoughtSummaries: [],
    synthesisModalOpen: false,
    synthesisContent: '',
    synthesisSubmitting: false,
    synthesisError: '',
    streak: config.streak ?? { days: 0, label: 'Start your first thinking streak today.' },
    threadModalOpen: false,
    threadLoading: false,
    threadThoughts: [],
    threadCurrentThoughtId: null,
    evolveModalOpen: false,
    evolveParentThought: null,
    evolveContent: '',
    evolvePriority: 'medium',
    evolveTags: '',
    evolveSubmitting: false,
    init() {
        this.searchQuery = config.initialSearch ?? '';
        this.debouncedSearch = debounce(() => this.fetchSearchResults(), 300);
        this.initializeSortables();
        this.loadRediscover();
        this.loadReviewSuggestions();

        if (this.searchQuery.trim() !== '') {
            this.fetchSearchResults();
        }
    },
    initializeSortables() {
        document.querySelectorAll('[data-thought-list]').forEach((element) => mountSortable(element));
    },
    applyTemplate(template) {
        this.quickThought = template.content ?? '';
    },
    applyPromptToQuickThought() {
        const prompt = this.activePrompt?.prompt?.trim();

        if (!prompt) {
            return;
        }

        this.quickThought = `${prompt}\n`;
    },
    useSuggestedPrompt() {
        if (!this.suggestedPrompt) {
            return;
        }

        this.activePrompt = this.suggestedPrompt;
        this.applyPromptToQuickThought();
    },
    isThoughtSelected(thoughtId) {
        return this.selectedThoughtIds.includes(thoughtId);
    },
    toggleThoughtSelection(thought) {
        if (this.isThoughtSelected(thought.id)) {
            this.selectedThoughtIds = this.selectedThoughtIds.filter((id) => id !== thought.id);
            this.selectedThoughtSummaries = this.selectedThoughtSummaries.filter((item) => item.id !== thought.id);
            return;
        }

        this.selectedThoughtIds = [...this.selectedThoughtIds, thought.id];
        this.selectedThoughtSummaries = [...this.selectedThoughtSummaries, thought];
    },
    clearThoughtSelection() {
        this.selectedThoughtIds = [];
        this.selectedThoughtSummaries = [];
    },
    applySynthesisSuggestion(suggestion) {
        this.selectedThoughtIds = [...suggestion.thought_ids];
        this.selectedThoughtSummaries = [...suggestion.thoughts];
        this.openSynthesisEditor();
    },
    openSynthesisEditor() {
        if (this.selectedThoughtIds.length < 2) {
            return;
        }

        this.synthesisContent = '';
        this.synthesisError = '';
        this.synthesisModalOpen = true;
    },
    async submitQuickThought() {
        const content = this.quickThought.trim();

        if (content === '' || this.quickSubmitting) {
            return;
        }

        this.quickSubmitting = true;
        this.quickError = '';
        this.quickSuccess = false;

        const list = document.querySelector(`[data-thought-list][data-stream-id="${config.firstStreamId}"]`);
        const tempId = `temp-${Date.now()}`;

        if (list) {
            const tempCard = document.createElement('article');
            tempCard.id = tempId;
            tempCard.className = 'rounded-[1.7rem] border border-white/10 bg-stone-950/70 p-4 opacity-70';
            tempCard.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <span class="priority-pill bg-amber-300/15 text-amber-200">medium</span>
                    <span class="text-[11px] uppercase tracking-[0.2em] text-stone-500">Saving</span>
                </div>
                <p class="mt-4 whitespace-pre-line text-sm leading-6 text-stone-100">${escapeHtml(content)}</p>
            `;
            list.prepend(tempCard);
        }

        try {
            const response = await fetch(config.quickThoughtUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                body: JSON.stringify({ content }),
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message ?? 'Unable to save thought.');
            }

            this.insertThoughtHtml(payload.thought.stream_id, payload.html, tempId);
            this.quickThought = '';
            this.quickSuccess = true;
            window.setTimeout(() => {
                this.quickSuccess = false;
            }, 1200);

            await Promise.all([
                this.loadRediscover(),
                this.loadReviewSuggestions(),
            ]);

            this.updateStreakAfterCapture();

            if (this.searchQuery.trim() !== '') {
                this.fetchSearchResults();
            }
        } catch (error) {
            document.getElementById(tempId)?.remove();
            this.quickError = error.message;
        } finally {
            this.quickSubmitting = false;
        }
    },
    async submitSynthesis() {
        if (this.selectedThoughtIds.length < 2 || this.synthesisContent.trim() === '' || this.synthesisSubmitting) {
            return;
        }

        this.synthesisSubmitting = true;
        this.synthesisError = '';

        try {
            const response = await fetch(config.synthesisStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                body: JSON.stringify({
                    content: this.synthesisContent,
                    thought_ids: this.selectedThoughtIds,
                }),
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message ?? 'Unable to synthesize thoughts.');
            }

            this.insertThoughtHtml(payload.thought.stream_id, payload.html);
            this.synthesisModalOpen = false;
            this.synthesisContent = '';
            this.clearThoughtSelection();
            this.updateStreakAfterCapture();
        } catch (error) {
            this.synthesisError = error.message;
        } finally {
            this.synthesisSubmitting = false;
        }
    },
    insertThoughtHtml(streamId, html, temporaryId = null) {
        const streamList = document.querySelector(`[data-thought-list][data-stream-id="${streamId}"]`);

        if (!streamList) {
            return;
        }

        if (temporaryId) {
            document.getElementById(temporaryId)?.remove();
        }

        streamList.insertAdjacentHTML('afterbegin', html);
        streamList.querySelectorAll('[data-empty-state]').forEach((node) => {
            node.remove();
        });

        const countNode = streamList.closest('.glass-panel')?.querySelector('[data-stream-count]');

        if (countNode) {
            countNode.textContent = String(Number.parseInt(countNode.textContent || '0', 10) + 1);
        }
    },
    onSearchInput() {
        if (this.searchQuery.trim() === '') {
            this.clearSearch();
            return;
        }

        this.searchPending = true;
        this.debouncedSearch();
    },
    async fetchSearchResults() {
        const query = this.searchQuery.trim();

        if (query === '') {
            this.clearSearch();
            return;
        }

        const url = new URL(config.searchUrl, window.location.origin);
        url.searchParams.set('q', query);

        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
            },
        });

        const payload = await response.json();
        const matchedIds = new Set(payload.thoughts.map((thought) => String(thought.id)));

        document.querySelectorAll('[data-thought-card]').forEach((card) => {
            const thoughtId = card.dataset.thoughtId;
            const contentNode = card.querySelector('[data-thought-content-display]');

            if (!matchedIds.has(thoughtId)) {
                card.classList.add('hidden');
                restoreThoughtCardContent(card);
                return;
            }

            const match = payload.thoughts.find((thought) => String(thought.id) === thoughtId);
            card.classList.remove('hidden');

            if (contentNode && match) {
                contentNode.innerHTML = match.highlighted_content;
            }
        });

        document.querySelectorAll('[data-thought-list]').forEach((list) => {
            const visibleCards = list.querySelectorAll('[data-thought-card]:not(.hidden)');
            const emptyState = list.querySelector('[data-empty-state]');

            if (visibleCards.length === 0) {
                if (!emptyState) {
                    const node = document.createElement('div');
                    node.dataset.emptyState = 'search';
                    node.className = 'rounded-[1.7rem] border border-dashed border-white/10 bg-white/[0.03] p-6 text-sm text-stone-400';
                    node.textContent = `No thoughts match "${query}" in this stream.`;
                    list.appendChild(node);
                }
            } else if (emptyState?.dataset.emptyState === 'search') {
                emptyState.remove();
            }
        });

        this.searchPending = false;
    },
    clearSearch() {
        this.searchPending = false;
        document.querySelectorAll('[data-thought-card]').forEach((card) => {
            card.classList.remove('hidden');
            restoreThoughtCardContent(card);
        });
        document.querySelectorAll('[data-empty-state="search"]').forEach((node) => node.remove());
    },
    async loadRediscover() {
        this.rediscoverLoading = true;
        const response = await fetch(config.rediscoverUrl, { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        this.rediscoverEntries = payload.entries ?? [];
        this.rediscoverLoading = false;
    },
    async loadReviewSuggestions() {
        this.reviewLoading = true;
        const response = await fetch(config.reviewUrl, { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        this.reviewThoughts = payload.thoughts ?? [];
        this.reviewLoading = false;
    },
    async markReview(thoughtId, reviewScore) {
        const url = config.reviewStoreUrlTemplate.replace('__THOUGHT__', thoughtId);
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
            },
            body: JSON.stringify({ review_score: reviewScore }),
        });

        if (response.ok) {
            this.reviewThoughts = this.reviewThoughts.filter((thought) => thought.id !== thoughtId);
            this.loadRediscover();
        }
    },
    async openThread(thoughtId) {
        this.threadModalOpen = true;
        this.threadLoading = true;
        const url = config.threadUrlTemplate.replace('__THOUGHT__', thoughtId);
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        this.threadThoughts = payload.thread ?? [];
        this.threadCurrentThoughtId = payload.current_thought_id ?? null;
        this.threadLoading = false;
    },
    openEvolveThought(payload) {
        this.evolveParentThought = payload;
        this.evolveContent = '';
        this.evolvePriority = payload.priority ?? 'medium';
        this.evolveTags = (payload.tags ?? []).join(', ');
        this.evolveModalOpen = true;
    },
    async submitEvolution() {
        if (!this.evolveParentThought || this.evolveSubmitting || this.evolveContent.trim() === '') {
            return;
        }

        this.evolveSubmitting = true;
        const url = config.evolveUrlTemplate.replace('__THOUGHT__', this.evolveParentThought.id);
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
            },
            body: JSON.stringify({
                content: this.evolveContent,
                priority: this.evolvePriority,
                tags: this.evolveTags,
            }),
        });
        const payload = await response.json();

        if (response.ok) {
            this.insertThoughtHtml(payload.thought.stream_id, payload.html);
            this.evolveModalOpen = false;
            this.loadRediscover();
            this.loadReviewSuggestions();
            this.openThread(this.evolveParentThought.id);
        }

        this.evolveSubmitting = false;
    },
    scrollToThought(thoughtId) {
        const node = document.getElementById(`thought-${thoughtId}`);

        if (!node) {
            return;
        }

        node.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'center',
        });
    },
    updateStreakAfterCapture() {
        if (this.streak.days === 0) {
            this.streak = {
                days: 1,
                label: 'You captured thoughts 1 day in a row.',
            };
        }
    },
}));

const DIAGRAM_SELECTOR = '.mermaid#thought-domain-event-flow, .mermaid[data-diagram="thought-domain-event-flow"]';
const DOCUMENT_URL = '/docs/architecture/thought-domain-event-flow.md';
const MERMAID_URL = 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs';

async function loadMarkdown(url) {
    const response = await fetch(url, {
        headers: {
            Accept: 'text/markdown, text/plain;q=0.9, */*;q=0.8',
        },
    });

    if (!response.ok) {
        throw new Error(`Unable to load diagram source: ${response.status}`);
    }

    return response.text();
}

function extractMermaidBlock(markdown) {
    const match = markdown.match(/```mermaid\s*([\s\S]*?)```/i);

    if (!match) {
        throw new Error('Mermaid block not found in architecture markdown.');
    }

    return match[1].trim();
}

async function loadMermaid() {
    const mermaid = (await import(MERMAID_URL)).default;

    mermaid.initialize({
        startOnLoad: false,
        theme: 'dark',
        securityLevel: 'loose',
        flowchart: {
            curve: 'basis',
            padding: 10,
            nodeSpacing: 60,
            rankSpacing: 110,
        },
        themeVariables: {
            background: 'transparent',
            primaryColor: '#1f2937',
            primaryBorderColor: '#60a5fa',
            primaryTextColor: '#e5e7eb',
            secondaryColor: '#022c22',
            secondaryBorderColor: '#34d399',
            secondaryTextColor: '#d1fae5',
            tertiaryColor: '#1e1b4b',
            tertiaryBorderColor: '#a78bfa',
            tertiaryTextColor: '#e9d5ff',
            lineColor: '#60a5fa',
            mainBkg: '#1f2937',
            nodeBorder: '#60a5fa',
            fontFamily: '"Space Grotesk", "DM Sans", sans-serif',
        },
    });

    return mermaid;
}

async function renderThoughtDomainEventFlow() {
    const target = document.querySelector(DIAGRAM_SELECTOR);

    if (!target) {
        return;
    }

    try {
        const markdown = await loadMarkdown(DOCUMENT_URL);
        const diagramDefinition = extractMermaidBlock(markdown);
        const mermaid = await loadMermaid();

        target.textContent = diagramDefinition;
        target.removeAttribute('data-diagram-error');

        await mermaid.run({
            nodes: [target],
        });

        const svg = target.querySelector('svg');

        if (svg) {
            svg.classList.add('mx-auto', 'h-auto', 'max-w-full');
        }
    } catch (error) {
        console.error(error);
        target.textContent = 'Unable to load the Thought Domain Event Architecture diagram.';
        target.setAttribute('data-diagram-error', 'true');
    }
}

void renderThoughtDomainEventFlow();

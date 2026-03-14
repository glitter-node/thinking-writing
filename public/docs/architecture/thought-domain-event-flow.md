# Thought Domain Event Flow

```mermaid
flowchart TB
    A(("Thought Action"))
    B(("Domain Event"))

    C(("Graph Index"))
    D(("Tag Index"))
    E(("Cooccurrence"))
    F(("Activity Log"))
    G(("Thought Version"))
    H(("Thinking Session"))

    A --> B

    B --> C
    B --> D
    B --> E
    B --> F
    B --> G
    B --> H

    classDef action fill:#1f2937,stroke:#60a5fa,color:#e5e7eb,stroke-width:2px;
    classDef event fill:#022c22,stroke:#34d399,color:#d1fae5,stroke-width:2px;
    classDef signal fill:#1e1b4b,stroke:#a78bfa,color:#e9d5ff,stroke-width:2px;

    class A action
    class B event
    class C,D,E,F,G,H signal
```

## Explanation

- Thought write actions dispatch domain events from the Thought services after persistence and link synchronization complete.
- Listener classes react to those events to keep the graph index, tag index, cooccurrence signals, activity log, versions, and thinking-session counters synchronized.
- The event-driven design keeps derived data updates out of the core write services, which reduces coupling and keeps the lifecycle pipeline easier to extend.

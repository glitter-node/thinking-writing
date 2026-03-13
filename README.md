# Glitter Thought Write

A personal thinking workspace for capturing, evolving, and connecting thoughts.

Repository name: `glitter-thought-board`

## Stack

- Laravel 12
- MySQL
- Blade + Alpine.js
- Tailwind CSS
- Sortable.js
- Laravel Breeze

## Authentication Methods

ThinkWrite supports two low-friction sign-in paths on the request access screen:

- Google One Tap through `POST /auth/google/onetap`
- Email Magic Link fallback through `POST /auth/email-link`

Request access UI:

- `GET /login`
- `GET /auth`
- `resources/views/auth/request-access.blade.php`

Google One Tap flow:

- the login page loads Google Identity Services when `GOOGLE_CLIENT_ID` is configured
- the browser posts the returned credential token to `POST /auth/google/onetap`
- `GoogleIdentityService` verifies the Google ID token through Google's token verification endpoint
- the backend validates issuer, audience, expiration, and `email_verified`
- the app reuses an existing user by verified email or creates a new account and starts the Laravel session

Email Magic Link flow:

- the fallback form accepts an email address
- ThinkWrite creates or finds the user account
- the app sends a 10 minute signed login URL through the mail system
- `GET /auth/magic/{token}` validates the signed URL, authenticates the user, and redirects to the dashboard

Mail templates:

- `resources/views/emails/magic-link.blade.php`
- `resources/views/emails/verify.blade.php`

## Email System

ThinkWrite includes a reusable SMTP-backed mail layer for verification emails and future system notifications.

Core pieces:

- `App\Services\MailService` is the single application mail entry point
- `App\Mail\VerifyEmail` renders the verification mailable
- `resources/views/emails/verify.blade.php` contains the HTML verification template

Verification flow:

- the registration flow creates the user
- ThinkWrite builds a signed verification URL for `verification.verify`
- the URL host is normalized against `BASE_URL` when present
- the app sends `VerifyEmail` through `MailService`
- the resend verification route uses the same mail path

SMTP configuration comes from the standard Laravel mail environment variables:

- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

This keeps email delivery infrastructure separate from the thinking engine while preserving a consistent service layer for future notification types.

## Thinking engine features

- Quick thought capture into the active space
- Thought evolution chains with parent-child relationships
- Knowledge graph linking with backlinks and graph exploration
- Thought synthesis for combining multiple ideas into a new direction
- Graph index engine for scalable neighbor traversal
- Idea emergence engine for statistical pattern discovery without AI
- Idea lifecycle system from thought to project execution
- Spatial thinking canvas for freely arranging thoughts on a workspace
- Guided thinking prompts and starter templates to avoid blank-state paralysis
- Daily thinking streaks based on thinking sessions
- Thread view for exploring how an idea changed over time
- Daily review suggestions to prevent idea decay
- Rediscovery sidebar with time-based resurfacing
- Instant thought search with MySQL fulltext support and SQLite fallback
- Drag and drop thought movement between streams
- Immutable thought history with versions, event logs, and exports

## Guided thinking system

The biggest UX risk in a personal thinking tool is opening a blank board and having no obvious next move.

ThinkWrite addresses that with a guided start layer at the top of every board:

- a daily prompt to trigger the first thought
- a smart suggestion based on recent topics
- optional starter templates such as `Idea`, `Observation`, `Problem`, `Question`, and `Insight`
- the quick thought input directly below the prompt so capture still happens in one step

Core pieces:

- `ThinkingPromptService::getDailyPrompt()`
- `ThinkingPromptService::getRandomPrompt()`
- `ThinkingPromptService::getCategoryPrompt()`
- `ThinkingPromptService::getSmartSuggestionForUser()`

Prompts are cached through the Redis store when available, with a safe cache fallback for environments where Redis is not running.

UI:

- `resources/views/components/thinking-prompt.blade.php`
- `resources/views/components/thought-template-selector.blade.php`

This keeps the board functional even for first-time or low-momentum sessions.

## Thinking streak logic

ThinkWrite tracks momentum through `thinking_sessions`.

Each time a real thought is captured or evolved, the current day's thinking session increments `thought_count`.

The streak card reports how many consecutive days the user has captured at least one thought.

Core pieces:

- `ThinkingSessionService::recordThought()`
- `ThinkingSessionService::getStreak()`

UI:

- `resources/views/components/thinking-streak.blade.php`

## Knowledge graph

ThinkWrite now supports bidirectional thought linking.

Inline syntax:

- `[[Distributed systems concept]]`
- `[[Implementation idea]]`

When a linked thought already exists in the same space, the parser creates an edge to it.
When it does not exist, ThinkWrite creates a placeholder thought and links to that node.

Implemented pieces:

- `thought_links`
- `ThoughtLinkService::parseLinks()`
- `ThoughtLinkService::createLinks()`
- `ThoughtLinkService::updateLinks()`
- `ThoughtGraphService::getSpaceGraph()`
- `ThoughtGraphService::getConnectedThoughts()`
- `GET /thoughts/{thought}/links`
- `GET /graph`

UI:

- thought cards show `Linked Thoughts`
- thought cards show `Referenced By`
- `resources/views/graph/index.blade.php` renders the graph explorer

This turns the board into a navigable thinking network rather than a flat note list.

## Graph Explorer

ThinkWrite includes a Cytoscape.js powered Graph Explorer at:

- `GET /graph`

API endpoints:

- `GET /api/thoughts/graph`
- `GET /api/thoughts/{thought}/neighbors`

Behavior:

- Laravel provides graph node and edge data
- Cytoscape.js renders the interactive graph in the browser
- the initial graph is capped at 100 thought nodes
- hovering a node lazy-loads local neighbors
- clicking a node opens that thought in its board context

Relationship colors:

- `link`
- `evolution`
- `synthesis`

Screenshot instructions:

- open `/graph`
- zoom and pan until the graph is framed the way you want
- capture the browser window with your normal screenshot tool

## Graph Focus Mode

ThinkWrite also supports focused graph exploration at:

- `GET /graph/{thought}`
- `GET /api/thoughts/{thought}/focus`

Focus mode behavior:

- one thought becomes the center node
- `1-hop` loads direct neighbors
- `2-hop` expands the graph to neighbors of neighbors
- backlinks can be toggled on or off
- synthesis edges can be toggled on or off
- clicking a node recenters the graph on that thought
- the center node is highlighted in orange while neighbor nodes stay teal

This creates a Roam or Obsidian style exploration flow around a single idea without loading the full graph.

## Graph Path Finder

ThinkWrite can find the shortest indexed connection between two thoughts.

Routes:

- `GET /graph/path`
- `GET /api/thoughts/path?from={id}&to={id}`

Behavior:

- uses breadth first search over indexed graph neighbors
- supports link, evolution, and synthesis relationships
- limits traversal depth to 6 hops
- limits visited nodes to 500 for safety
- returns the shortest discovered path as a sequence of thought IDs
- highlights and animates the path inside Cytoscape

This helps users navigate the knowledge graph when two ideas are related indirectly rather than through a direct edge.

## Canvas thinking mode

ThinkWrite now includes a spatial workspace at:

- `GET /canvas`

The canvas lets users:

- position thoughts freely with persisted `x` and `y` coordinates
- drag one thought or a selected cluster of thoughts
- pan and zoom around the active space
- view link, evolution, and synthesis edges on the same surface
- lazy load viewport data through `GET /spaces/{space}/canvas`
- persist coordinates through `POST /thoughts/{thought}/position`

Storage:

- `thought_positions`

Fields:

- `thought_id`
- `space_id`
- `x`
- `y`

Core pieces:

- `CanvasService::getCanvas()`
- `ThoughtPositionService::store()`

UI:

- `resources/views/canvas/index.blade.php`
- `resources/js/canvas.js`

Clusters are currently lightweight spatial groupings built from selected thoughts and stream-based suggestions so users can move related ideas together without changing the underlying thought graph.

## Thought synthesis

ThinkWrite can combine multiple thoughts into a new synthesized thought.

Flow:

- select two or more thought cards
- open the synthesis editor
- write the combined thought
- save the synthesis

The synthesized thought keeps explicit source references through:

- `thought_syntheses`
- `thought_synthesis_items`

The synthesized output displays `Synthesized From` on its card, and synthesis edges are included in the knowledge graph so source thoughts point into the new combined idea.

Core pieces:

- `ThoughtSynthesisService::createSynthesis()`
- `ThoughtSuggestionService::getSynthesisSuggestions()`
- `POST /spaces/{space}/syntheses`

UI:

- `resources/views/components/thought-multiselect.blade.php`
- `resources/views/components/synthesis-panel.blade.php`
- `resources/views/components/synthesis-editor.blade.php`

Synthesis complements the rest of the system:

- evolution deepens a single thought over time
- linking connects related thoughts
- synthesis merges multiple thoughts into a new idea

## Graph index engine

As the knowledge graph grows, recursive traversal becomes the main performance risk.

ThinkWrite now stores precomputed adjacency rows in `thought_graph_index` so graph exploration can use indexed lookups instead of recursive joins at request time.

Stored fields:

- `thought_id`
- `linked_thought_id`
- `link_type`
- `depth`

Link types:

- `direct`
- `evolution`
- `synthesis`

Core pieces:

- `ThoughtGraphIndexService::updateGraphIndex()`
- `ThoughtGraphIndexService::rebuildGraphIndex()`
- `ThoughtGraphIndexService::getConnectedThoughts()`
- `ThoughtGraphTraversalService::getConnectedThoughts()`
- `GET /thoughts/{thought}/graph`

The index updates whenever:

- inline links change
- a thought evolves
- a synthesis is created

## Graph cache strategy

Neighbor traversals are cached with keys in this form:

- `thought_graph:{thought_id}:{depth}`

Redis is used when available, with a safe fallback cache path for environments where Redis is not running.

Cache TTL:

- 1 hour for Redis-backed neighbor sets

Cache invalidation runs whenever an indexed thought is rebuilt.

## Scalability

Scalability protections now include:

- indexed adjacency rows instead of recursive traversal
- depth-limited traversal responses
- cached neighbor lookups
- `RebuildGraphIndexJob` for consistency rebuilds
- daily scheduling in `routes/console.php`

## Idea emergence engine

ThinkWrite can surface hidden connections without AI by indexing tags and scoring repeated structural patterns.

The emergence system looks at:

- shared tags
- shared links
- time proximity
- co-occurrence inside syntheses

New storage:

- `thought_tag_index`
- `thought_cooccurrence`

Core pieces:

- `ThoughtEmergenceService::calculateTagClusters()`
- `ThoughtEmergenceService::calculateCooccurrence()`
- `ThoughtEmergenceService::suggestConnections()`
- `GET /thoughts/{thought}/suggestions`
- `GET /emergence`

Scoring logic:

- 3 or more shared tags increases score significantly
- appearing together inside syntheses increases score
- direct graph links increase score
- close creation times add a smaller boost

UI:

- `resources/views/components/emerging-ideas.blade.php`
- `resources/views/emergence/index.blade.php`

This helps users discover new insights from the graph structure itself instead of relying on external models or APIs.

## Idea lifecycle system

ThinkWrite now supports the full execution path of an idea.

Lifecycle stages:

- `thought`
- `concept`
- `project`
- `task`
- `outcome`

Thoughts can be promoted through these stages and turned into real execution objects.

New storage:

- `thoughts.stage`
- `projects`
- `tasks`

Core pieces:

- `IdeaLifecycleService::promoteThoughtToConcept()`
- `IdeaLifecycleService::createProjectFromThought()`
- `IdeaLifecycleService::createTasksFromProject()`
- `IdeaLifecycleService::completeTask()`

UI:

- lifecycle controls on thought cards
- `/projects` kanban board

Graph integration:

- thought -> project
- project -> task

This lets the system move from idea development into execution while preserving the original thinking context.

## Thought evolution

Thoughts are no longer isolated notes.

Each thought can evolve into a new thought through `parent_id`:

`idea -> refined idea -> implementation idea`

Implemented pieces:

- `ThoughtEvolutionService::createEvolution()`
- `ThoughtEvolutionService::getThoughtThread()`
- `GET /thoughts/{thought}/thread`
- `POST /thoughts/{thought}/evolve`

UI:

- `resources/views/components/evolve-thought-modal.blade.php`
- `resources/views/components/thought-thread.blade.php`

## Review system

Glitter includes a review loop to avoid the “idea graveyard” problem.

`thought_reviews` stores:

- `thought_id`
- `reviewed_at`
- `review_score`

Daily review suggestions are selected by:

- high priority first
- least recently reviewed thoughts first
- randomization within the candidate set

Implemented pieces:

- `ThoughtReviewService::getDailyReviewSet()`
- `POST /thoughts/{thought}/reviews`
- `GET /spaces/{space}/reviews`

UI:

- `resources/views/components/review-panel.blade.php`

## Search architecture

Search stays on the existing route:

- `GET /spaces/{space}/search`

Search implementation:

- MySQL uses `MATCH(content) AGAINST(...)` fulltext search
- SQLite test runs fall back to `LIKE`

Controllers stay thin and delegate search shaping to services and repositories.

## Immutable thought model

ThinkWrite now preserves thought history instead of treating edits as destructive overwrites.

Storage:

- `thought_versions`
- `thought_events`

Edit behavior:

- creating a thought records version `1`
- editing a thought appends a new version row
- the current thought row still acts as the live projection for fast board rendering and search
- delete actions archive thoughts through soft deletion instead of permanently removing them

Version services:

- `ThoughtVersionService::createInitialVersion()`
- `ThoughtVersionService::createVersion()`
- `ThoughtVersionService::getVersionHistory()`

Event services:

- `ThoughtEventService::recordEvent()`
- `ThoughtEventService::getThoughtEvents()`

Recorded event types include:

- `ThoughtCreated`
- `ThoughtEdited`
- `ThoughtLinked`
- `ThoughtSynthesized`
- `ThoughtPromoted`
- `ThoughtReviewed`
- `ThoughtArchived`

UI:

- board cards expose a `Version history` panel
- recent event types are shown directly on the thought card

This preserves the chain of thinking rather than hiding how an idea changed.

## Export

ThinkWrite can export thought history at:

- `GET /export/thoughts`

Formats:

- `?format=json`
- `?format=markdown`

Exports include current thought data, version history, event history, space/stream context, and archive status.

## Versioning

ThinkWrite uses Git tags as the primary application version source.

Examples:

- `v0.1.0`
- `v0.2.0`
- `v0.3.0`
- `v1.0.0`

Version rules:

- semantic versioning is the default strategy
- `php artisan app:version` reports the current application version
- the UI footer and about page display the active version
- `APP_VERSION` can override Git detection when needed
- if no tag exists, the application falls back to the current short commit hash

## Favicon

ThinkWrite stores generated favicon assets in:

- `public/favicon/`

Included files:

- `favicon.ico`
- `favicon-32x32.png`
- `favicon-16x16.png`
- `apple-touch-icon.png`
- `site.webmanifest`

These files are integrated through the main application layout without changing any of the existing logo or icon assets used elsewhere in the project.

## Thinking Kernel architecture

ThinkWrite now has a platform layer that sits above the domain services.

Top-level flow:

`Controller -> Core -> Modules`

The kernel lives in `app/Core` and keeps only essential orchestration concerns:

- `app/Core/Thought`
  - `ThoughtKernel.php`
- `app/Core/Graph`
  - `GraphKernel.php`
- `app/Core/Search`
  - `SearchKernel.php`
- `app/Core/Index`
  - `IndexKernel.php`
- `app/Core/ModuleManager.php`
- `app/Core/Contracts/ThinkingModuleInterface.php`

Kernel responsibilities:

- expose stable entrypoints for thought, graph, search, and index operations
- dispatch domain events after core mutations
- invoke registered modules through a shared module contract
- keep controllers from depending on many feature-specific services directly

This keeps ThinkWrite extensible without turning controllers into feature coordinators.

## Module system

Feature extensions now live in `app/Modules`:

- `PromptModule`
- `ReviewModule`
- `EvolutionModule`
- `SynthesisModule`
- `EmergenceModule`
- `LifecycleModule`

Each module implements `ThinkingModuleInterface`:

- `register()`
- `boot()`
- `processThought()`

Module loading is handled by `ModuleManager`, which:

- discovers module classes from `app/Modules`
- registers them during application startup
- boots event listeners and module wiring
- allows the kernels to pass changed thoughts through the active module pipeline

This makes features additive. The core stays focused on essential thinking primitives, while modules attach specialized behavior around prompts, reviews, emergence, lifecycle execution, and other higher-level workflows.

## Event-driven thinking pipeline

The kernel emits events when core thought actions complete:

- `ThoughtCreated`
- `ThoughtLinked`
- `ThoughtSynthesized`
- `ThoughtReviewed`

Modules listen to these events to refresh or react without pushing that logic back into controllers.

Examples:

- emergence listeners can refresh tag and co-occurrence indexes
- review listeners can refresh review state
- synthesis listeners can attach downstream graph or suggestion behavior

This event-driven pipeline allows ThinkWrite to grow as a long-term platform while preserving the existing feature set.

## Architecture

Glitter uses a domain-oriented structure.

Request flow:

`Controller -> Core -> Service -> Repository -> Model`

### Domains

- `app/Domain/Space`
  - `Models/Space.php`
  - `Repositories/SpaceRepository.php`
  - `Services/SpaceService.php`
- `app/Domain/Stream`
  - `Models/Stream.php`
  - `Repositories/StreamRepository.php`
  - `Services/StreamService.php`
- `app/Domain/Thought`
  - `Models/Thought.php`
  - `Repositories/ThoughtRepository.php`
  - `Repositories/ThoughtEvolutionRepository.php`
  - `Services/ThoughtService.php`
  - `Services/ThoughtEvolutionService.php`
  - `Services/IdeaLifecycleService.php`
  - `Services/ThoughtLinkService.php`
  - `Services/ThoughtGraphIndexService.php`
  - `Services/ThoughtGraphService.php`
  - `Services/ThoughtGraphTraversalService.php`
- `app/Domain/Project`
  - `Models/Project.php`
  - `Repositories/ProjectRepository.php`
  - `Services/ProjectService.php`
- `app/Domain/Task`
  - `Models/Task.php`
  - `Repositories/TaskRepository.php`
  - `Services/TaskService.php`
- `app/Domain/ThoughtLink`
  - `Models/ThoughtLink.php`
  - `Repositories/ThoughtLinkRepository.php`
- `app/Domain/ThoughtGraphIndex`
  - `Models/ThoughtGraphIndex.php`
  - `Repositories/ThoughtGraphIndexRepository.php`
- `app/Domain/ThoughtEmergence`
  - `Models/ThoughtTagIndex.php`
  - `Models/ThoughtCooccurrence.php`
  - `Repositories/ThoughtTagIndexRepository.php`
  - `Repositories/ThoughtCooccurrenceRepository.php`
  - `Services/ThoughtEmergenceService.php`
- `app/Domain/ThoughtReview`
  - `Models/ThoughtReview.php`
  - `Repositories/ThoughtReviewRepository.php`
  - `Services/ThoughtReviewService.php`
- `app/Domain/ThoughtSynthesis`
  - `Models/ThoughtSynthesis.php`
  - `Models/ThoughtSynthesisItem.php`
  - `Repositories/ThoughtSynthesisRepository.php`
  - `Services/ThoughtSynthesisService.php`
  - `Services/ThoughtSuggestionService.php`
- `app/Domain/ThinkingPrompt`
  - `Models/ThinkingPrompt.php`
  - `Repositories/ThinkingPromptRepository.php`
  - `Services/ThinkingPromptService.php`
- `app/Domain/ThinkingSession`
  - `Models/ThinkingSession.php`
  - `Repositories/ThinkingSessionRepository.php`
  - `Services/ThinkingSessionService.php`
- `app/Domain/ThoughtPosition`
  - `Models/ThoughtPosition.php`
  - `Repositories/ThoughtPositionRepository.php`
  - `Services/CanvasService.php`
  - `Services/ThoughtPositionService.php`
- `app/Domain/ThoughtVersion`
  - `Models/ThoughtVersion.php`
  - `Repositories/ThoughtVersionRepository.php`
  - `Services/ThoughtVersionService.php`
- `app/Domain/ThoughtEvent`
  - `Models/ThoughtEvent.php`
  - `Repositories/ThoughtEventRepository.php`
  - `Services/ThoughtEventService.php`

### Responsibilities

- Controllers handle HTTP concerns, route authorization, and response formatting.
- Core kernels coordinate essential thinking workflows and module execution.
- Services own workflows and transactions.
- Repositories own persistence and query strategy.
- Models define relationships, casts, and domain persistence shape.

## Database indexing strategy

Glitter is indexed for large thought collections.

Indexes in use:

- `spaces.user_id`
  - covered by the foreign key index
- `streams.space_id`
  - covered by the foreign key index
- `streams.position`
  - indexed explicitly
- `thoughts.stream_id`
  - covered by the foreign key index
- `thoughts.user_id`
  - covered by the foreign key index
- `thoughts.parent_id`
  - indexed explicitly
- `thoughts.position`
  - indexed explicitly and also used in composite ordering indexes
- `thoughts.content`
  - MySQL fulltext index
- `thought_reviews.thought_id`
  - covered by the foreign key index
- `thought_reviews.reviewed_at`
  - indexed explicitly
- `thought_reviews(thought_id, reviewed_at)`
  - indexed explicitly for review history lookup
- `thought_links.source_thought_id`
  - indexed explicitly
- `thought_links.target_thought_id`
  - indexed explicitly
- `thought_links(source_thought_id, target_thought_id)`
  - unique for duplicate edge prevention
- `thinking_prompts.category`
  - indexed explicitly for category prompt lookup
- `thinking_sessions(user_id, started_at)`
  - indexed explicitly for streak and daily session lookup
- `thought_syntheses.user_id`
  - indexed explicitly
- `thought_syntheses.synthesized_thought_id`
  - indexed explicitly for source reference lookup
- `thought_synthesis_items.synthesis_id`
  - indexed explicitly
- `thought_synthesis_items.thought_id`
  - indexed explicitly
- `thought_graph_index.thought_id`
  - indexed explicitly for neighbor lookup
- `thought_graph_index.linked_thought_id`
  - indexed explicitly for reverse lookup and rebuild impact
- `thought_graph_index.depth`
  - indexed explicitly for depth-limited traversal
- `thought_tag_index.tag`
  - indexed explicitly for tag clustering
- `thought_tag_index.thought_id`
  - indexed explicitly for reverse tag lookup
- `thought_cooccurrence.thought_a_id`
  - indexed explicitly for suggestion lookup
- `thought_cooccurrence.thought_b_id`
  - indexed explicitly for suggestion lookup
- `thoughts.stage`
  - indexed explicitly for lifecycle filtering
- `projects.thought_id`
  - indexed explicitly for lifecycle lookup
- `tasks.project_id`
  - indexed explicitly for project task lookup

These indexes support:

- latest-thought queries
- space search
- thread retrieval
- review selection
- ordered stream rendering
- graph edge traversal
- backlink lookups
- prompt selection by category
- thinking streak calculations
- synthesis lookup
- synthesis graph traversal
- indexed graph neighbor traversal
- cached graph exploration
- tag clustering
- co-occurrence suggestion generation
- lifecycle stage filtering
- project and task board rendering

## Frontend structure

- `resources/views/spaces/show.blade.php` is the board shell
- `resources/views/components/quick-thought.blade.php` handles instant capture
- `resources/views/components/search-box.blade.php` handles live search
- `resources/views/components/review-panel.blade.php` handles daily review
- `resources/views/components/rediscover-panel.blade.php` handles resurfacing
- `resources/views/components/evolve-thought-modal.blade.php` handles evolution creation
- `resources/views/components/thought-thread.blade.php` handles thread display
- `resources/views/graph/index.blade.php` handles graph exploration
- `resources/views/canvas/index.blade.php` handles spatial canvas exploration
- `resources/views/components/thinking-prompt.blade.php` handles guided starts
- `resources/views/components/thought-template-selector.blade.php` handles starter templates
- `resources/views/components/thinking-streak.blade.php` handles momentum tracking
- `resources/views/components/thought-multiselect.blade.php` handles source selection
- `resources/views/components/synthesis-panel.blade.php` handles synthesis suggestions
- `resources/views/components/synthesis-editor.blade.php` handles synthesis creation
- `resources/views/components/emerging-ideas.blade.php` handles statistical idea suggestions
- `resources/views/emergence/index.blade.php` handles emergence dashboards
- `resources/views/projects/index.blade.php` handles execution projects and tasks
- `resources/js/board.js` contains board interactions
- `resources/js/graph.js` contains graph rendering and exploration
- `resources/js/canvas.js` contains spatial canvas interactions

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Run

```bash
php artisan serve
```

In another shell:

```bash
npm run dev
```

## Tests

Feature coverage includes:

- Space CRUD
- Standard thought creation
- Quick thought creation
- Thought movement
- Thought evolution
- Thought linking and placeholder creation
- Backlinks and graph API
- Prompt generation and smart prompt selection
- Thinking session tracking and streak calculation
- Thought synthesis and graph integration
- Canvas loading and thought position persistence
- Graph index rebuild and cached traversal
- Idea emergence, co-occurrence scoring, and suggestion endpoints
- Idea lifecycle promotion, project creation, and task completion
- Thought version history and immutable event logging
- Thought review suggestions and recording
- Thought search
- Rediscover endpoint
- Authorization boundaries

Run:

```bash
php artisan test
```

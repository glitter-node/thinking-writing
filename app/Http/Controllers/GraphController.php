<?php

namespace App\Http\Controllers;

use App\Domain\Space\Models\Space;
use App\Domain\Space\Services\SpaceService;
use App\Domain\Thought\Models\Thought;
use App\Services\ThoughtGraphService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GraphController extends Controller
{
    public function __construct(
        private readonly SpaceService $spaceService,
        private readonly ThoughtGraphService $thoughtGraphService,
    ) {
    }

    public function index(Request $request): View
    {
        $spaces = $this->spaceService->listForUser($request->user());
        $space = $spaces->firstWhere('id', (int) $request->integer('space')) ?? $spaces->first();

        abort_if(! $space, 404);
        $this->authorize('view', $space);

        return view('graph.index', [
            'spaces' => $spaces,
            'currentSpace' => $space,
            'graphApiUrl' => route('api.thoughts.graph', ['space' => $space->id]),
            'neighborsUrlTemplate' => url('/api/thoughts/__THOUGHT__/neighbors'),
            'focusApiUrlTemplate' => url('/api/thoughts/__THOUGHT__/focus'),
            'pathApiUrl' => route('api.thoughts.path'),
            'focusThought' => null,
            'pathMode' => false,
            'thoughtOptions' => $space->thoughts()
                ->orderBy('thoughts.id')
                ->get(['thoughts.id', 'thoughts.content']),
            'selectedFromThought' => null,
            'selectedToThought' => null,
        ]);
    }

    public function focus(Request $request, Thought $thought): View
    {
        $this->authorize('view', $thought);
        $spaces = $this->spaceService->listForUser($request->user());
        $currentSpace = $spaces->firstWhere('id', $thought->stream->space_id) ?? $thought->stream->space;

        return view('graph.index', [
            'spaces' => $spaces,
            'currentSpace' => $currentSpace,
            'graphApiUrl' => route('api.thoughts.graph', ['space' => $currentSpace->id]),
            'neighborsUrlTemplate' => url('/api/thoughts/__THOUGHT__/neighbors'),
            'focusApiUrlTemplate' => url('/api/thoughts/__THOUGHT__/focus'),
            'pathApiUrl' => route('api.thoughts.path'),
            'focusThought' => $thought,
            'pathMode' => false,
            'thoughtOptions' => $currentSpace->thoughts()
                ->orderBy('thoughts.id')
                ->get(['thoughts.id', 'thoughts.content']),
            'selectedFromThought' => null,
            'selectedToThought' => null,
        ]);
    }

    public function path(Request $request): View
    {
        $spaces = $this->spaceService->listForUser($request->user());
        $space = $spaces->firstWhere('id', (int) $request->integer('space')) ?? $spaces->first();

        abort_if(! $space, 404);
        $this->authorize('view', $space);

        $fromThought = $request->filled('from')
            ? $space->thoughts()->where('thoughts.id', (int) $request->integer('from'))->first()
            : null;
        $toThought = $request->filled('to')
            ? $space->thoughts()->where('thoughts.id', (int) $request->integer('to'))->first()
            : null;

        return view('graph.index', [
            'spaces' => $spaces,
            'currentSpace' => $space,
            'graphApiUrl' => route('api.thoughts.graph', ['space' => $space->id]),
            'neighborsUrlTemplate' => url('/api/thoughts/__THOUGHT__/neighbors'),
            'focusApiUrlTemplate' => url('/api/thoughts/__THOUGHT__/focus'),
            'pathApiUrl' => route('api.thoughts.path'),
            'focusThought' => null,
            'pathMode' => true,
            'thoughtOptions' => $space->thoughts()
                ->orderBy('thoughts.id')
                ->get(['thoughts.id', 'thoughts.content']),
            'selectedFromThought' => $fromThought,
            'selectedToThought' => $toThought,
        ]);
    }

    public function graphData(Request $request): JsonResponse
    {
        $spaces = $this->spaceService->listForUser($request->user());
        $space = $spaces->firstWhere('id', (int) $request->integer('space')) ?? $spaces->first();

        abort_if(! $space, 404);
        $this->authorize('view', $space);

        return response()->json(
            $this->thoughtGraphService->getGraphData($space),
        );
    }

    public function neighbors(Thought $thought): JsonResponse
    {
        $this->authorize('view', $thought);

        return response()->json(
            $this->thoughtGraphService->getNeighbors($thought),
        );
    }

    public function focusData(Request $request, Thought $thought): JsonResponse
    {
        $this->authorize('view', $thought);

        return response()->json(
            $this->thoughtGraphService->getFocusedGraph(
                $thought,
                (int) $request->integer('depth', 1),
                $request->boolean('backlinks', true),
                $request->boolean('syntheses', true),
                50,
            ),
        );
    }

    public function pathData(Request $request): JsonResponse
    {
        $fromThought = Thought::query()->with(['stream.space'])->findOrFail((int) $request->integer('from'));
        $toThought = Thought::query()->with(['stream.space'])->findOrFail((int) $request->integer('to'));

        $this->authorize('view', $fromThought);
        $this->authorize('view', $toThought);
        abort_if($fromThought->stream->space_id !== $toThought->stream->space_id, 422, 'Thoughts must be in the same space.');

        return response()->json(
            $this->thoughtGraphService->findPath($fromThought, $toThought),
        );
    }
}

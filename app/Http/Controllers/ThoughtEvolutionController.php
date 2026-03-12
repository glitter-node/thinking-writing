<?php

namespace App\Http\Controllers;

use App\Core\Thought\ThoughtKernel;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Requests\EvolveThoughtRequest;
use App\Domain\Thought\Services\ThoughtEvolutionService;
use Illuminate\Http\JsonResponse;

class ThoughtEvolutionController extends Controller
{
    public function __construct(
        private readonly ThoughtEvolutionService $thoughtEvolutionService,
        private readonly ThoughtKernel $thoughtKernel,
    ) {
    }

    public function store(EvolveThoughtRequest $request, Thought $thought): JsonResponse
    {
        $newThought = $this->thoughtKernel->evolve($thought, [
            'content' => $request->validated('content'),
            'priority' => $request->validated('priority') ?? $thought->priority,
            'tags' => $this->thoughtKernel->normalizeTags($request->input('tags', $thought->tags ?? [])),
        ]);

        return response()->json([
            'message' => 'Thought evolved.',
            'thought' => [
                'id' => $newThought->id,
                'stream_id' => $newThought->stream_id,
                'parent_id' => $newThought->parent_id,
            ],
            'html' => view('components.spaces.board.thought-card', [
                'thought' => $newThought,
            ])->render(),
        ], 201);
    }

    public function show(Thought $thought): JsonResponse
    {
        $this->authorize('update', $thought);

        return response()->json([
            'thread' => $this->thoughtEvolutionService
                ->getThoughtThread($thought)
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'parent_id' => $item->parent_id,
                    'content' => $item->content,
                    'stream_title' => $item->stream->title,
                    'created_at_human' => $item->created_at->diffForHumans(),
                ])
                ->all(),
            'current_thought_id' => $thought->id,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Core\Thought\ThoughtKernel;
use App\Domain\Space\Models\Space;
use App\Domain\ThoughtSynthesis\Requests\StoreThoughtSynthesisRequest;
use Illuminate\Http\JsonResponse;

class ThoughtSynthesisController extends Controller
{
    public function __construct(
        private readonly ThoughtKernel $thoughtKernel,
    ) {
    }

    public function store(StoreThoughtSynthesisRequest $request, Space $space): JsonResponse
    {
        $thought = $this->thoughtKernel->synthesize(
            $space,
            $request->user(),
            $request->validated('thought_ids'),
            $request->validated('content'),
        );

        return response()->json([
            'message' => 'Thought synthesized.',
            'thought' => [
                'id' => $thought->id,
                'stream_id' => $thought->stream_id,
            ],
            'html' => view('components.spaces.board.thought-card', [
                'thought' => $thought,
            ])->render(),
        ], 201);
    }
}

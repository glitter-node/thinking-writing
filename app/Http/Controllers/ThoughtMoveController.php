<?php

namespace App\Http\Controllers;

use App\Core\Thought\ThoughtKernel;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Requests\MoveThoughtRequest;
use Illuminate\Http\JsonResponse;

class ThoughtMoveController extends Controller
{
    public function __construct(private readonly ThoughtKernel $thoughtKernel)
    {
    }

    public function __invoke(MoveThoughtRequest $request, Thought $thought): JsonResponse
    {
        $movedThought = $this->thoughtKernel->move(
            $thought,
            $request->integer('stream_id'),
            $request->integer('position'),
        );

        return response()->json([
            'message' => 'Thought moved.',
            'thought_id' => $movedThought->id,
            'stream_id' => $movedThought->stream_id,
            'position' => $movedThought->position,
        ]);
    }
}

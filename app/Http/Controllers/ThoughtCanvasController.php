<?php

namespace App\Http\Controllers;

use App\Domain\Space\Models\Space;
use App\Domain\Space\Services\SpaceService;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtPosition\Requests\StoreThoughtPositionRequest;
use App\Domain\ThoughtPosition\Services\CanvasService;
use App\Domain\ThoughtPosition\Services\ThoughtPositionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThoughtCanvasController extends Controller
{
    public function __construct(
        private readonly SpaceService $spaceService,
        private readonly CanvasService $canvasService,
        private readonly ThoughtPositionService $thoughtPositionService,
    ) {
    }

    public function index(Request $request): View
    {
        $spaces = $this->spaceService->listForUser($request->user());
        $space = $spaces->firstWhere('id', (int) $request->integer('space')) ?? $spaces->first();

        if (! $space) {
            return view('empty.spaces', [
                'context' => 'canvas',
            ]);
        }

        $this->authorize('view', $space);

        return view('canvas.index', [
            'spaces' => $spaces,
            'currentSpace' => $space,
            'initialCanvas' => $this->canvasService->getCanvas($space),
        ]);
    }

    public function show(Request $request, Space $space): JsonResponse
    {
        $this->authorize('view', $space);

        return response()->json(
            $this->canvasService->getCanvas($space, [
                'x' => $request->integer('x'),
                'y' => $request->integer('y'),
                'width' => $request->integer('width'),
                'height' => $request->integer('height'),
            ]),
        );
    }

    public function storePosition(StoreThoughtPositionRequest $request, Thought $thought): JsonResponse
    {
        $this->authorize('update', $thought);

        return response()->json([
            'message' => 'Thought position saved.',
            'position' => $this->thoughtPositionService->store($thought, $request->validated()),
        ]);
    }
}

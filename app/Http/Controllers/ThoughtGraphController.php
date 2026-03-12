<?php

namespace App\Http\Controllers;

use App\Core\Graph\GraphKernel;
use App\Domain\Space\Services\SpaceService;
use App\Domain\Thought\Models\Thought;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThoughtGraphController extends Controller
{
    public function __construct(
        private readonly SpaceService $spaceService,
        private readonly GraphKernel $graphKernel,
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
            'graph' => $this->graphKernel->getSpaceGraph($space),
        ]);
    }

    public function links(Thought $thought): JsonResponse
    {
        $this->authorize('view', $thought);

        return response()->json(
            $this->graphKernel->getConnectedThoughts($thought, 2),
        );
    }

    public function graph(Request $request, Thought $thought): JsonResponse
    {
        $this->authorize('view', $thought);

        return response()->json(
            $this->graphKernel->getConnectedThoughts(
                $thought,
                (int) $request->integer('depth', 2),
            ),
        );
    }
}

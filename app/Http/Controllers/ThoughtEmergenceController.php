<?php

namespace App\Http\Controllers;

use App\Domain\Space\Services\SpaceService;
use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtEmergence\Services\ThoughtEmergenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThoughtEmergenceController extends Controller
{
    public function __construct(
        private readonly ThoughtEmergenceService $thoughtEmergenceService,
        private readonly SpaceService $spaceService,
    ) {
    }

    public function suggestions(Thought $thought): JsonResponse
    {
        $this->authorize('view', $thought);

        return response()->json(
            $this->thoughtEmergenceService->suggestConnections($thought),
        );
    }

    public function index(Request $request): View
    {
        $spaces = $this->spaceService->listForUser($request->user());
        $space = $spaces->firstWhere('id', (int) $request->integer('space')) ?? $spaces->first();

        return view('emergence.index', [
            'spaces' => $spaces,
            'currentSpace' => $space,
            'dashboard' => $this->thoughtEmergenceService->getDashboard($request->user()->id, $space),
        ]);
    }
}

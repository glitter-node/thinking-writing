<?php

namespace App\Http\Controllers;

use App\Core\Search\SearchKernel;
use App\Domain\Space\Models\Space;
use App\Domain\Space\Requests\StoreSpaceRequest;
use App\Domain\Space\Requests\UpdateSpaceRequest;
use App\Domain\Space\Services\SpaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpaceController extends Controller
{
    public function __construct(
        private readonly SpaceService $spaceService,
        private readonly SearchKernel $searchKernel,
    ) {
    }

    public function index(Request $request): View
    {
        return view('spaces.index', [
            'spaces' => $this->spaceService->listForUser($request->user()),
        ]);
    }

    public function store(StoreSpaceRequest $request): RedirectResponse
    {
        $space = $this->spaceService->create($request->user(), $request->validated());

        return redirect()
            ->route('spaces.show', $space)
            ->with('status', 'Space created.');
    }

    public function show(Request $request, Space $space): View
    {
        $this->authorize('view', $space);

        $search = trim((string) $request->string('q'));
        $board = $this->spaceService->getBoard($space, $request->user(), $search);

        return view('spaces.show', [
            'space' => $board['space'],
            'emergenceSuggestions' => $board['emergenceSuggestions'],
            'promptPack' => $board['promptPack'],
            'search' => $search,
            'synthesisSuggestions' => $board['synthesisSuggestions'],
            'streak' => $board['streak'],
            'streamOptions' => $board['streamOptions'],
        ]);
    }

    public function update(UpdateSpaceRequest $request, Space $space): RedirectResponse
    {
        $this->authorize('update', $space);

        $this->spaceService->update($space, $request->validated());

        return redirect()
            ->route('spaces.show', $space)
            ->with('status', 'Space updated.');
    }

    public function destroy(Request $request, Space $space): RedirectResponse
    {
        $this->authorize('delete', $space);

        $this->spaceService->delete($space);

        return redirect()
            ->route('spaces.index')
            ->with('status', 'Space deleted.');
    }

    public function search(Request $request, Space $space): JsonResponse
    {
        $this->authorize('view', $space);

        return response()->json([
            'thoughts' => $this->searchKernel->searchThoughts($space, (string) $request->string('q')),
        ]);
    }

    public function rediscover(Space $space): JsonResponse
    {
        $this->authorize('view', $space);

        return response()->json([
            'entries' => $this->spaceService->rediscover($space),
        ]);
    }

    public function reviews(Request $request, Space $space): JsonResponse
    {
        $this->authorize('view', $space);

        return response()->json([
            'thoughts' => $this->spaceService->getReviewSuggestions($request->user()->id, $space),
        ]);
    }
}

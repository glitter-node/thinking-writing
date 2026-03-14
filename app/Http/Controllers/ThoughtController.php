<?php

namespace App\Http\Controllers;

use App\Core\Thought\ThoughtKernel;
use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Requests\QuickStoreThoughtRequest;
use App\Domain\Thought\Requests\StoreThoughtRequest;
use App\Domain\Thought\Requests\UpdateThoughtRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ThoughtController extends Controller
{
    public function __construct(private readonly ThoughtKernel $thoughtKernel)
    {
    }

    public function quickStore(QuickStoreThoughtRequest $request, Space $space): JsonResponse
    {
        $thought = $this->thoughtKernel->createQuick($space, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Thought captured.',
            'thought' => [
                'id' => $thought->id,
                'stream_id' => $thought->stream_id,
                'content' => $thought->content,
                'priority' => $thought->priority,
            ],
            'html' => view('components.spaces.board.thought-card', [
                'thought' => $thought,
            ])->render(),
        ], 201);
    }

    public function store(StoreThoughtRequest $request, Stream $stream): RedirectResponse
    {
        $this->thoughtKernel->create($stream, $request->user(), $request->validated());

        return redirect()
            ->route('spaces.show', $stream->space)
            ->with('status', 'Thought captured.');
    }

    public function update(UpdateThoughtRequest $request, Thought $thought): RedirectResponse
    {
        $this->thoughtKernel->update($thought, $request->validated());

        return redirect()
            ->route('spaces.show', $thought->stream->space)
            ->with('status', 'Thought updated.');
    }

    public function destroy(Thought $thought): RedirectResponse
    {
        $this->authorize('delete', $thought);

        $space = $thought->stream->space;
        $this->thoughtKernel->delete($thought);

        return redirect()
            ->route('spaces.show', $space)
            ->with('status', 'Thought deleted.');
    }
}

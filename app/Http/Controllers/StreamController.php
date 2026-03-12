<?php

namespace App\Http\Controllers;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\Requests\StoreStreamRequest;
use App\Domain\Stream\Requests\UpdateStreamRequest;
use App\Domain\Stream\Services\StreamService;
use Illuminate\Http\RedirectResponse;

class StreamController extends Controller
{
    public function __construct(private readonly StreamService $streamService)
    {
    }

    public function store(StoreStreamRequest $request, Space $space): RedirectResponse
    {
        $this->streamService->create($space, $request->validated());

        return redirect()
            ->route('spaces.show', $space)
            ->with('status', 'Stream created.');
    }

    public function update(UpdateStreamRequest $request, Stream $stream): RedirectResponse
    {
        $this->streamService->update($stream, $request->validated());

        return redirect()
            ->route('spaces.show', $stream->space)
            ->with('status', 'Stream updated.');
    }

    public function destroy(Stream $stream): RedirectResponse
    {
        $this->authorize('delete', $stream);

        $space = $stream->space;
        $this->streamService->delete($stream);

        return redirect()
            ->route('spaces.show', $space)
            ->with('status', 'Stream deleted.');
    }
}

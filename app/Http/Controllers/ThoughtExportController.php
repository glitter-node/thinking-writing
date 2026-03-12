<?php

namespace App\Http\Controllers;

use App\Domain\Thought\Services\ThoughtExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThoughtExportController extends Controller
{
    public function __construct(
        private readonly ThoughtExportService $thoughtExportService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $format = $request->string('format')->lower()->value() === 'markdown' ? 'markdown' : 'json';
        $content = $this->thoughtExportService->export($request->user(), $format);
        $contentType = $format === 'markdown' ? 'text/markdown' : 'application/json';
        $filename = 'thinkwrite-thoughts.'.$format;

        return response($content, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Domain\Project\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('projects.index', [
            'projects' => $this->projectService->getBoard($request->user()->id),
        ]);
    }
}

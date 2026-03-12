<?php

use App\Http\Controllers\GraphController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\ThoughtController;
use App\Http\Controllers\ThoughtCanvasController;
use App\Http\Controllers\ThoughtEmergenceController;
use App\Http\Controllers\ThoughtExportController;
use App\Http\Controllers\ThoughtEvolutionController;
use App\Http\Controllers\ThoughtGraphController;
use App\Http\Controllers\IdeaLifecycleController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ThoughtMoveController;
use App\Http\Controllers\ThoughtReviewController;
use App\Http\Controllers\ThoughtSynthesisController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::view('/about', 'about')->name('about');

Route::get('/dashboard', function () {
    return redirect()->route('spaces.index');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/graph', [GraphController::class, 'index'])->name('graph.index');
    Route::get('/graph/path', [GraphController::class, 'path'])->name('graph.path');
    Route::get('/graph/{thought}', [GraphController::class, 'focus'])->name('graph.focus');
    Route::get('/canvas', [ThoughtCanvasController::class, 'index'])->name('canvas.index');
    Route::get('/api/thoughts/graph', [GraphController::class, 'graphData'])->name('api.thoughts.graph');
    Route::get('/api/thoughts/path', [GraphController::class, 'pathData'])->name('api.thoughts.path');
    Route::get('/api/thoughts/{thought}/focus', [GraphController::class, 'focusData'])->name('api.thoughts.focus');
    Route::get('/api/thoughts/{thought}/neighbors', [GraphController::class, 'neighbors'])->name('api.thoughts.neighbors');
    Route::get('/export/thoughts', ThoughtExportController::class)->name('thoughts.export');
    Route::get('/emergence', [ThoughtEmergenceController::class, 'index'])->name('emergence.index');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::resource('spaces', SpaceController::class)->except(['create', 'edit']);
    Route::get('spaces/{space}/canvas', [ThoughtCanvasController::class, 'show'])->name('spaces.canvas');
    Route::get('spaces/{space}/search', [SpaceController::class, 'search'])->name('spaces.search');
    Route::get('spaces/{space}/rediscover', [SpaceController::class, 'rediscover'])->name('spaces.rediscover');
    Route::get('spaces/{space}/reviews', [SpaceController::class, 'reviews'])->name('spaces.reviews');
    Route::post('spaces/{space}/syntheses', [ThoughtSynthesisController::class, 'store'])->name('spaces.syntheses.store');

    Route::post('spaces/{space}/streams', [StreamController::class, 'store'])->name('spaces.streams.store');
    Route::post('spaces/{space}/quick-thoughts', [ThoughtController::class, 'quickStore'])->name('spaces.quick-thoughts.store');
    Route::patch('streams/{stream}', [StreamController::class, 'update'])->name('streams.update');
    Route::delete('streams/{stream}', [StreamController::class, 'destroy'])->name('streams.destroy');

    Route::post('streams/{stream}/thoughts', [ThoughtController::class, 'store'])->name('streams.thoughts.store');
    Route::patch('thoughts/{thought}', [ThoughtController::class, 'update'])->name('thoughts.update');
    Route::delete('thoughts/{thought}', [ThoughtController::class, 'destroy'])->name('thoughts.destroy');
    Route::post('thoughts/{thought}/position', [ThoughtCanvasController::class, 'storePosition'])->name('thoughts.position.store');
    Route::patch('thoughts/{thought}/move', ThoughtMoveController::class)->name('thoughts.move');
    Route::get('thoughts/{thought}/graph', [ThoughtGraphController::class, 'graph'])->name('thoughts.graph');
    Route::get('thoughts/{thought}/links', [ThoughtGraphController::class, 'links'])->name('thoughts.links');
    Route::get('thoughts/{thought}/suggestions', [ThoughtEmergenceController::class, 'suggestions'])->name('thoughts.suggestions');
    Route::get('thoughts/{thought}/thread', [ThoughtEvolutionController::class, 'show'])->name('thoughts.thread');
    Route::post('thoughts/{thought}/evolve', [ThoughtEvolutionController::class, 'store'])->name('thoughts.evolve');
    Route::post('thoughts/{thought}/promote', [IdeaLifecycleController::class, 'promote'])->name('thoughts.promote');
    Route::post('thoughts/{thought}/projects', [IdeaLifecycleController::class, 'createProject'])->name('thoughts.projects.store');
    Route::post('thoughts/{thought}/reviews', [ThoughtReviewController::class, 'store'])->name('thoughts.reviews.store');
    Route::post('projects/{project}/tasks', [IdeaLifecycleController::class, 'createTasks'])->name('projects.tasks.store');
    Route::patch('tasks/{task}/complete', [IdeaLifecycleController::class, 'completeTask'])->name('tasks.complete');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

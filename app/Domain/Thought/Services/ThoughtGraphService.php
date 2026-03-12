<?php

namespace App\Domain\Thought\Services;

use App\Domain\Project\Repositories\ProjectRepository;
use App\Domain\Space\Models\Space;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Repositories\ThoughtRepository;
use App\Domain\ThoughtLink\Repositories\ThoughtLinkRepository;
use App\Domain\ThoughtSynthesis\Repositories\ThoughtSynthesisRepository;

class ThoughtGraphService
{
    public function __construct(
        private readonly ThoughtRepository $thoughtRepository,
        private readonly ThoughtLinkRepository $thoughtLinkRepository,
        private readonly ThoughtSynthesisRepository $thoughtSynthesisRepository,
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function getSpaceGraph(Space $space): array
    {
        $thoughts = $this->thoughtRepository->getGraphThoughtsWithinSpace($space);
        $links = $this->thoughtRepository->getGraphLinksWithinSpace($space);
        $evolutions = $this->thoughtRepository->getEvolutionEdgesWithinSpace($space);
        $syntheses = $this->thoughtSynthesisRepository->getSynthesisEdgesForSpace($space->id);
        $projects = $this->projectRepository->withinSpace($space->id);

        return [
            'nodes' => $thoughts->map(fn (Thought $thought): array => [
                'id' => 'thought-'.$thought->id,
                'resource_id' => $thought->id,
                'kind' => 'thought',
                'label' => $this->summarize($thought->content),
                'content' => $thought->content,
                'stream_title' => $thought->stream->title,
                'space_id' => $space->id,
                'href' => route('spaces.show', $space).'#thought-'.$thought->id,
            ])->concat(
                $projects->map(fn ($project): array => [
                    'id' => 'project-'.$project->id,
                    'resource_id' => $project->id,
                    'kind' => 'project',
                    'label' => $this->summarize($project->title),
                    'content' => $project->title,
                    'stream_title' => 'Project',
                    'space_id' => $space->id,
                    'href' => route('projects.index').'#project-'.$project->id,
                ])
            )->concat(
                $projects->flatMap(fn ($project) => $project->tasks->map(fn ($task): array => [
                    'id' => 'task-'.$task->id,
                    'resource_id' => $task->id,
                    'kind' => 'task',
                    'label' => $this->summarize($task->title),
                    'content' => $task->title,
                    'stream_title' => 'Task',
                    'space_id' => $space->id,
                    'href' => route('projects.index').'#project-'.$project->id,
                ]))
            )->values()->all(),
            'edges' => $links->map(fn ($link): array => [
                'id' => $link->id,
                'source' => 'thought-'.$link->source_thought_id,
                'target' => 'thought-'.$link->target_thought_id,
                'type' => 'link',
            ])->concat(
                $evolutions->map(fn (Thought $thought): array => [
                    'id' => 'evolution-'.$thought->id,
                    'source' => 'thought-'.$thought->parent_id,
                    'target' => 'thought-'.$thought->id,
                    'type' => 'evolution',
                ])
            )->concat(
                $syntheses->flatMap(function ($synthesis) {
                    return $synthesis->items->map(fn ($item): array => [
                        'id' => 'synthesis-'.$synthesis->id.'-'.$item->thought_id,
                        'source' => 'thought-'.$item->thought_id,
                        'target' => 'thought-'.$synthesis->synthesized_thought_id,
                        'type' => 'synthesis',
                    ]);
                })
            )->concat(
                $projects->map(fn ($project): array => [
                    'id' => 'project-thought-'.$project->id,
                    'source' => 'thought-'.$project->thought_id,
                    'target' => 'project-'.$project->id,
                    'type' => 'project',
                ])
            )->concat(
                $projects->flatMap(fn ($project) => $project->tasks->map(fn ($task): array => [
                    'id' => 'project-task-'.$project->id.'-'.$task->id,
                    'source' => 'project-'.$project->id,
                    'target' => 'task-'.$task->id,
                    'type' => 'task',
                ]))
            )->values()->all(),
        ];
    }

    public function getConnectedThoughts(Thought $thought): array
    {
        $links = $this->thoughtLinkRepository->getConnectedLinks($thought);
        $synthesisItems = $this->thoughtRepository->getConnectedSynthesisItemsForThought($thought);

        $nodes = collect([$thought])
            ->merge($links->pluck('sourceThought'))
            ->merge($links->pluck('targetThought'))
            ->merge($synthesisItems->pluck('thought'))
            ->merge($synthesisItems->pluck('synthesis.synthesizedThought'))
            ->filter()
            ->unique('id')
            ->values();

        return [
            'thought' => [
                'id' => $thought->id,
                'content' => $thought->content,
            ],
            'nodes' => $nodes->map(fn (Thought $item): array => [
                'id' => $item->id,
                'content' => $item->content,
                'stream_title' => $item->stream->title,
            ])->all(),
            'edges' => $links->map(fn ($link): array => [
                'source' => $link->source_thought_id,
                'target' => $link->target_thought_id,
                'type' => 'link',
            ])->concat(
                $synthesisItems->map(fn ($item): array => [
                    'source' => $item->thought_id,
                    'target' => $item->synthesis->synthesized_thought_id,
                    'type' => 'synthesis',
                ])
            )->values()->all(),
        ];
    }

    private function summarize(string $content): string
    {
        $singleLine = trim(preg_replace('/\s+/', ' ', $content) ?? $content);

        return mb_strimwidth($singleLine, 0, 36, '...');
    }
}

<?php

namespace App\Core\Thought;

use App\Core\ModuleManager;
use App\Domain\Project\Models\Project;
use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use App\Domain\Task\Models\Task;
use App\Domain\Thought\Models\Thought;
use App\Domain\Thought\Services\IdeaLifecycleService;
use App\Domain\Thought\Services\ThoughtEvolutionService;
use App\Domain\Thought\Services\ThoughtService;
use App\Domain\ThoughtEvent\Services\ThoughtEventService;
use App\Domain\ThoughtReview\Services\ThoughtReviewService;
use App\Domain\ThoughtSynthesis\Services\ThoughtSynthesisService;
use App\Events\ThoughtCreated;
use App\Events\ThoughtLinked;
use App\Events\ThoughtReviewed;
use App\Events\ThoughtSynthesized;
use App\Models\User;
use App\Domain\ThoughtReview\Models\ThoughtReview;
use Illuminate\Support\Facades\Event;

class ThoughtKernel
{
    public function __construct(
        private readonly ThoughtService $thoughtService,
        private readonly ThoughtEvolutionService $thoughtEvolutionService,
        private readonly ThoughtSynthesisService $thoughtSynthesisService,
        private readonly ThoughtReviewService $thoughtReviewService,
        private readonly IdeaLifecycleService $ideaLifecycleService,
        private readonly ThoughtEventService $thoughtEventService,
        private readonly ModuleManager $moduleManager,
    ) {
    }

    public function create(Stream $stream, User $user, array $data): Thought
    {
        $thought = $this->thoughtService->create($stream, $user, $data);

        $this->afterThoughtMutation($thought, ['action' => 'create']);
        Event::dispatch(new ThoughtCreated($thought, ['action' => 'create']));
        Event::dispatch(new ThoughtLinked($thought, ['action' => 'create']));

        return $thought;
    }

    public function createQuick(Space $space, User $user, array $data): Thought
    {
        $thought = $this->thoughtService->createQuick($space, $user, $data);

        $this->afterThoughtMutation($thought, ['action' => 'quick_create']);
        Event::dispatch(new ThoughtCreated($thought, ['action' => 'quick_create']));
        Event::dispatch(new ThoughtLinked($thought, ['action' => 'quick_create']));

        return $thought;
    }

    public function update(Thought $thought, array $data): Thought
    {
        $thought = $this->thoughtService->update($thought, $data);

        $this->afterThoughtMutation($thought, ['action' => 'update']);
        Event::dispatch(new ThoughtLinked($thought, ['action' => 'update']));

        return $thought;
    }

    public function delete(Thought $thought): void
    {
        $this->thoughtService->delete($thought);
    }

    public function move(Thought $thought, int $targetStreamId, int $targetPosition): Thought
    {
        $thought = $this->thoughtService->move($thought, $targetStreamId, $targetPosition);

        $this->afterThoughtMutation($thought, ['action' => 'move']);

        return $thought;
    }

    public function evolve(Thought $thought, array $data): Thought
    {
        $newThought = $this->thoughtEvolutionService->createEvolution($thought, $data);

        $this->afterThoughtMutation($newThought, ['action' => 'evolve']);
        Event::dispatch(new ThoughtCreated($newThought, ['action' => 'evolve']));
        Event::dispatch(new ThoughtLinked($newThought, ['action' => 'evolve']));

        return $newThought;
    }

    public function synthesize(Space $space, User $user, array $thoughtIds, string $content): Thought
    {
        $thought = $this->thoughtSynthesisService->createSynthesis($space, $user, $thoughtIds, $content);

        $this->afterThoughtMutation($thought, ['action' => 'synthesize']);
        Event::dispatch(new ThoughtSynthesized($thought, ['source_thought_ids' => $thoughtIds]));
        Event::dispatch(new ThoughtLinked($thought, ['action' => 'synthesize']));

        return $thought;
    }

    public function review(Thought $thought, string $score): ThoughtReview
    {
        $review = $this->thoughtReviewService->recordReview($thought, $score);

        $this->thoughtEventService->recordEvent($thought, 'ThoughtReviewed', [
            'review_score' => $score,
        ]);
        $this->moduleManager->processThought($thought, ['action' => 'review', 'review_score' => $score]);
        Event::dispatch(new ThoughtReviewed($thought, ['review_score' => $score]));

        return $review;
    }

    public function promoteToConcept(Thought $thought): Thought
    {
        $thought = $this->ideaLifecycleService->promoteThoughtToConcept($thought);
        $this->afterThoughtMutation($thought, ['action' => 'promote']);

        return $thought;
    }

    public function createProject(Thought $thought): Project
    {
        $project = $this->ideaLifecycleService->createProjectFromThought($thought);
        $this->afterThoughtMutation($project->thought, ['action' => 'create_project']);

        return $project;
    }

    public function createTasks(Project $project): Project
    {
        $project = $this->ideaLifecycleService->createTasksFromProject($project);
        $this->afterThoughtMutation($project->thought, ['action' => 'create_tasks']);

        return $project;
    }

    public function completeTask(Task $task): Task
    {
        $task = $this->ideaLifecycleService->completeTask($task);
        $this->afterThoughtMutation($task->project->thought, ['action' => 'complete_task']);

        return $task;
    }

    public function normalizeTags(array|string|null $tags): array
    {
        return $this->thoughtService->normalizeTags($tags);
    }

    private function afterThoughtMutation(Thought $thought, array $context): void
    {
        $this->moduleManager->processThought($thought, $context);
    }
}

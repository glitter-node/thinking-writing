<?php

namespace Tests\Feature\Endpoints;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Feature\Endpoints\Concerns\BuildsWorkspaceFixtures;
use Tests\TestCase;

class EndpointAccessMatrixTest extends TestCase
{
    use BuildsWorkspaceFixtures;
    use RefreshDatabase;

    public function test_public_pages_render(): void
    {
        $this->get(route('welcome'))->assertOk();
        $this->get(route('about'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
        $this->get(route('password.request'))->assertOk();
        $this->get(route('password.reset', ['token' => 'test-token']))->assertOk();
    }

    public function test_guest_is_redirected_from_authenticated_routes(): void
    {
        $user = User::factory()->create();
        $space = $this->createSpaceFor($user);
        $stream = $this->createStreamFor($space, ['position' => 1]);
        $thought = $this->createThoughtFor($user, $stream, ['position' => 1]);
        $project = $this->createProjectForThought($thought);
        $task = $this->createTaskForProject($project);
        $verifyUrl = URL::temporarySignedRoute('verification.verify', now()->addMinutes(5), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $requests = [
            ['type' => 'redirect', 'response' => fn () => $this->get(route('dashboard'))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('graph.index'))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('graph.path', ['space' => $space->id]))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('graph.focus', $thought))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('canvas.index', ['space' => $space->id]))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('api.thoughts.graph', ['space' => $space->id]))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('api.thoughts.path', ['from' => $thought->id, 'to' => $thought->id]))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('api.thoughts.focus', $thought))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('api.thoughts.neighbors', $thought))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('thoughts.export'))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('emergence.index', ['space' => $space->id]))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('projects.index'))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('spaces.index'))],
            ['type' => 'redirect', 'response' => fn () => $this->post(route('spaces.store'), [])],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('spaces.show', $space))],
            ['type' => 'redirect', 'response' => fn () => $this->patch(route('spaces.update', $space), [])],
            ['type' => 'redirect', 'response' => fn () => $this->delete(route('spaces.destroy', $space))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('spaces.canvas', $space))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('spaces.search', $space))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('spaces.rediscover', $space))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('spaces.reviews', $space))],
            ['type' => 'json', 'response' => fn () => $this->postJson(route('spaces.syntheses.store', $space), [])],
            ['type' => 'redirect', 'response' => fn () => $this->post(route('spaces.streams.store', $space), [])],
            ['type' => 'json', 'response' => fn () => $this->postJson(route('spaces.quick-thoughts.store', $space), [])],
            ['type' => 'redirect', 'response' => fn () => $this->patch(route('streams.update', $stream), [])],
            ['type' => 'redirect', 'response' => fn () => $this->delete(route('streams.destroy', $stream))],
            ['type' => 'redirect', 'response' => fn () => $this->post(route('streams.thoughts.store', $stream), [])],
            ['type' => 'redirect', 'response' => fn () => $this->patch(route('tasks.complete', $task), [])],
            ['type' => 'redirect', 'response' => fn () => $this->patch(route('thoughts.update', $thought), [])],
            ['type' => 'redirect', 'response' => fn () => $this->delete(route('thoughts.destroy', $thought))],
            ['type' => 'json', 'response' => fn () => $this->postJson(route('thoughts.evolve', $thought), [])],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('thoughts.graph', $thought))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('thoughts.links', $thought))],
            ['type' => 'json', 'response' => fn () => $this->patchJson(route('thoughts.move', $thought), [])],
            ['type' => 'json', 'response' => fn () => $this->postJson(route('thoughts.position.store', $thought), [])],
            ['type' => 'redirect', 'response' => fn () => $this->post(route('thoughts.projects.store', $thought), [])],
            ['type' => 'redirect', 'response' => fn () => $this->post(route('thoughts.promote', $thought), [])],
            ['type' => 'json', 'response' => fn () => $this->postJson(route('thoughts.reviews.store', $thought), [])],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('thoughts.suggestions', $thought))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('thoughts.thread', $thought))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('profile.edit'))],
            ['type' => 'redirect', 'response' => fn () => $this->patch(route('profile.update'), [])],
            ['type' => 'redirect', 'response' => fn () => $this->delete(route('profile.destroy'), [])],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('verification.notice'))],
            ['type' => 'redirect', 'response' => fn () => $this->get($verifyUrl)],
            ['type' => 'redirect', 'response' => fn () => $this->post(route('verification.send'))],
            ['type' => 'redirect', 'response' => fn () => $this->get(route('password.confirm'))],
            ['type' => 'redirect', 'response' => fn () => $this->post(route('password.confirm'))],
            ['type' => 'redirect', 'response' => fn () => $this->put(route('password.update'))],
            ['type' => 'redirect', 'response' => fn () => $this->post(route('logout'))],
        ];

        foreach ($requests as $request) {
            $response = $request['response']();

            if ($request['type'] === 'json') {
                $response->assertUnauthorized();
                continue;
            }

            $response->assertRedirect(route('login'));
        }
    }

    public function test_authenticated_user_can_reach_protected_get_routes(): void
    {
        $user = User::factory()->unverified()->create();
        $space = $this->createSpaceFor($user);
        $stream = $this->createStreamFor($space, ['position' => 1]);
        $thought = $this->createThoughtFor($user, $stream, ['position' => 1, 'content' => 'Owner thought']);
        $project = $this->createProjectForThought($thought);
        $task = $this->createTaskForProject($project);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('spaces.index'));

        $responses = [
            $this->actingAs($user)->get(route('graph.index', ['space' => $space->id])),
            $this->actingAs($user)->get(route('graph.path', ['space' => $space->id, 'from' => $thought->id, 'to' => $thought->id])),
            $this->actingAs($user)->get(route('graph.focus', $thought)),
            $this->actingAs($user)->get(route('canvas.index', ['space' => $space->id])),
            $this->actingAs($user)->get(route('api.thoughts.graph', ['space' => $space->id])),
            $this->actingAs($user)->get(route('api.thoughts.path', ['from' => $thought->id, 'to' => $thought->id])),
            $this->actingAs($user)->get(route('api.thoughts.focus', $thought)),
            $this->actingAs($user)->get(route('api.thoughts.neighbors', $thought)),
            $this->actingAs($user)->get(route('thoughts.export')),
            $this->actingAs($user)->get(route('emergence.index', ['space' => $space->id])),
            $this->actingAs($user)->get(route('projects.index')),
            $this->actingAs($user)->get(route('spaces.index')),
            $this->actingAs($user)->get(route('spaces.show', $space)),
            $this->actingAs($user)->get(route('spaces.canvas', $space)),
            $this->actingAs($user)->get(route('spaces.search', ['space' => $space->id, 'q' => 'Owner'])),
            $this->actingAs($user)->get(route('spaces.rediscover', $space)),
            $this->actingAs($user)->get(route('spaces.reviews', $space)),
            $this->actingAs($user)->get(route('thoughts.graph', $thought)),
            $this->actingAs($user)->get(route('thoughts.links', $thought)),
            $this->actingAs($user)->get(route('thoughts.suggestions', $thought)),
            $this->actingAs($user)->get(route('thoughts.thread', $thought)),
            $this->actingAs($user)->get(route('profile.edit')),
            $this->actingAs($user)->get(route('verification.notice')),
            $this->actingAs($user)->get(route('password.confirm')),
        ];

        foreach ($responses as $response) {
            $response->assertOk();
        }

        $this->actingAs($user)
            ->get(route('thoughts.export'))
            ->assertHeader('content-disposition', 'attachment; filename="thinkwrite-thoughts.json"');

        $this->assertSame('todo', $task->status);
    }

    public function test_non_owner_cannot_access_resource_bound_routes(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $space = $this->createSpaceFor($owner);
        $stream = $this->createStreamFor($space, ['position' => 1]);
        $thought = $this->createThoughtFor($owner, $stream, ['position' => 1]);

        $requests = [
            fn () => $this->actingAs($intruder)->get(route('spaces.show', $space)),
            fn () => $this->actingAs($intruder)->get(route('spaces.canvas', $space)),
            fn () => $this->actingAs($intruder)->get(route('spaces.search', ['space' => $space->id, 'q' => 'x'])),
            fn () => $this->actingAs($intruder)->get(route('spaces.rediscover', $space)),
            fn () => $this->actingAs($intruder)->get(route('spaces.reviews', $space)),
            fn () => $this->actingAs($intruder)->get(route('graph.focus', $thought)),
            fn () => $this->actingAs($intruder)->get(route('api.thoughts.focus', $thought)),
            fn () => $this->actingAs($intruder)->get(route('api.thoughts.neighbors', $thought)),
            fn () => $this->actingAs($intruder)->get(route('thoughts.graph', $thought)),
            fn () => $this->actingAs($intruder)->get(route('thoughts.links', $thought)),
            fn () => $this->actingAs($intruder)->get(route('thoughts.suggestions', $thought)),
            fn () => $this->actingAs($intruder)->get(route('thoughts.thread', $thought)),
            fn () => $this->actingAs($intruder)->get(route('api.thoughts.path', ['from' => $thought->id, 'to' => $thought->id])),
        ];

        foreach ($requests as $request) {
            $request()->assertForbidden();
        }
    }
}

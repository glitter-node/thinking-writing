<?php

namespace Tests\Feature\Endpoints;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\Feature\Endpoints\Concerns\BuildsWorkspaceFixtures;
use Tests\TestCase;

class EndpointMutationMatrixTest extends TestCase
{
    use BuildsWorkspaceFixtures;
    use RefreshDatabase;

    public function test_public_auth_mutations_validate_and_succeed(): void
    {
        $this->post(route('register'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->post(route('register'), [
            'name' => 'Endpoint User',
            'email' => 'endpoint@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'endpoint@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);

        auth()->logout();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        auth()->logout();

        Notification::fake();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['password']);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_account_management_mutations_validate_and_succeed(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('password.confirm'), ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');

        $this->actingAs($user)
            ->post(route('password.confirm'), ['password' => 'password'])
            ->assertRedirect();

        $this->actingAs($user)
            ->from('/profile')
            ->put(route('password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'another-password',
                'password_confirmation' => 'another-password',
            ])
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');

        $this->actingAs($user)
            ->from('/profile')
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'another-password',
                'password_confirmation' => 'another-password',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('another-password', $user->fresh()->password));

        $this->actingAs($user)
            ->patch(route('profile.update'), [])
            ->assertSessionHasErrors(['name', 'email']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Updated Endpoint User',
                'email' => 'updated-endpoint@example.com',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Endpoint User',
            'email' => 'updated-endpoint@example.com',
        ]);

        $this->actingAs($user)
            ->from('/profile')
            ->delete(route('profile.destroy'), ['password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'another-password'])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_space_and_stream_mutations_validate_authorize_and_succeed(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $space = $this->createSpaceFor($owner, ['title' => 'Workspace']);
        $stream = $this->createStreamFor($space, ['title' => 'Inbox', 'position' => 1]);

        $this->actingAs($owner)
            ->post(route('spaces.store'), [])
            ->assertSessionHasErrors('title');

        $this->actingAs($owner)
            ->post(route('spaces.store'), [
                'title' => 'New Space',
                'description' => 'Created in endpoint matrix.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('spaces', [
            'title' => 'New Space',
        ]);

        $this->actingAs($intruder)
            ->patch(route('spaces.update', $space), [
                'title' => 'Intruder edit',
                'description' => 'Denied',
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->patch(route('spaces.update', $space), [])
            ->assertSessionHasErrors('title');

        $this->actingAs($owner)
            ->patch(route('spaces.update', $space), [
                'title' => 'Workspace Updated',
                'description' => 'Updated description.',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('spaces', [
            'id' => $space->id,
            'title' => 'Workspace Updated',
        ]);

        $this->actingAs($owner)
            ->post(route('spaces.streams.store', $space), [])
            ->assertSessionHasErrors('title');

        $this->actingAs($owner)
            ->post(route('spaces.streams.store', $space), ['title' => 'Captured'])
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('streams', [
            'space_id' => $space->id,
            'title' => 'Captured',
        ]);

        $this->actingAs($intruder)
            ->patch(route('streams.update', $stream), ['title' => 'Denied'])
            ->assertForbidden();

        $this->actingAs($owner)
            ->patch(route('streams.update', $stream), [])
            ->assertSessionHasErrors('title');

        $this->actingAs($owner)
            ->patch(route('streams.update', $stream), ['title' => 'Inbox Updated'])
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('streams', [
            'id' => $stream->id,
            'title' => 'Inbox Updated',
        ]);

        $this->actingAs($intruder)
            ->delete(route('streams.destroy', $stream))
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete(route('streams.destroy', $stream))
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseMissing('streams', [
            'id' => $stream->id,
        ]);
    }

    public function test_thought_mutations_validate_authorize_and_succeed(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $space = $this->createSpaceFor($owner);
        $stream = $this->createStreamFor($space, ['position' => 1]);
        $thought = $this->createThoughtFor($owner, $stream, ['content' => 'Existing thought', 'position' => 1]);

        $this->actingAs($owner)
            ->post(route('streams.thoughts.store', $stream), [])
            ->assertSessionHasErrors(['content', 'priority']);

        $this->actingAs($owner)
            ->post(route('streams.thoughts.store', $stream), [
                'content' => 'Fresh thought',
                'priority' => 'high',
                'tags' => 'alpha,beta',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('thoughts', [
            'stream_id' => $stream->id,
            'content' => 'Fresh thought',
            'priority' => 'high',
        ]);

        $this->actingAs($owner)
            ->postJson(route('spaces.quick-thoughts.store', $space), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('content');

        $quickResponse = $this->actingAs($owner)
            ->postJson(route('spaces.quick-thoughts.store', $space), [
                'content' => 'Quick endpoint thought',
            ])
            ->assertCreated()
            ->assertJsonPath('thought.content', 'Quick endpoint thought');

        $quickThoughtId = $quickResponse->json('thought.id');
        $this->assertDatabaseHas('thoughts', [
            'id' => $quickThoughtId,
            'content' => 'Quick endpoint thought',
        ]);

        $this->actingAs($intruder)
            ->patch(route('thoughts.update', $thought), [
                'content' => 'Denied update',
                'priority' => 'low',
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->patch(route('thoughts.update', $thought), [])
            ->assertSessionHasErrors(['content', 'priority']);

        $this->actingAs($owner)
            ->patch(route('thoughts.update', $thought), [
                'content' => 'Updated thought',
                'priority' => 'low',
                'tags' => 'updated',
            ])
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('thoughts', [
            'id' => $thought->id,
            'content' => 'Updated thought',
            'priority' => 'low',
        ]);

        $this->actingAs($intruder)
            ->delete(route('thoughts.destroy', $thought))
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete(route('thoughts.destroy', $thought))
            ->assertRedirect(route('spaces.show', $space));

        $this->assertSoftDeleted((new Thought())->getTable(), [
            'id' => $thought->id,
        ]);
    }

    public function test_advanced_thought_mutations_validate_authorize_and_succeed(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $space = $this->createSpaceFor($owner);
        $streamA = $this->createStreamFor($space, ['position' => 1]);
        $streamB = $this->createStreamFor($space, ['position' => 2]);
        $otherSpace = $this->createSpaceFor($owner);
        $otherStream = $this->createStreamFor($otherSpace, ['position' => 1]);
        $thoughtA = $this->createThoughtFor($owner, $streamA, ['content' => 'Base thought', 'position' => 1]);
        $thoughtB = $this->createThoughtFor($owner, $streamA, ['content' => 'Pair thought', 'position' => 2]);

        $this->actingAs($owner)
            ->patchJson(route('thoughts.move', $thoughtA), [
                'stream_id' => $otherStream->id,
                'position' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('stream_id');

        $this->actingAs($intruder)
            ->patchJson(route('thoughts.move', $thoughtA), [
                'stream_id' => $streamB->id,
                'position' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->patchJson(route('thoughts.move', $thoughtA), [
                'stream_id' => $streamB->id,
                'position' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('stream_id', $streamB->id);

        $this->assertDatabaseHas('thoughts', [
            'id' => $thoughtA->id,
            'stream_id' => $streamB->id,
            'position' => 1,
        ]);

        $this->actingAs($owner)
            ->postJson(route('thoughts.position.store', $thoughtA), [
                'x' => 99999,
                'y' => 0,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('x');

        $this->actingAs($intruder)
            ->postJson(route('thoughts.position.store', $thoughtA), [
                'x' => 10,
                'y' => 20,
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->postJson(route('thoughts.position.store', $thoughtA), [
                'x' => 10,
                'y' => 20,
            ])
            ->assertOk()
            ->assertJsonPath('position.x', 10)
            ->assertJsonPath('position.y', 20);

        $this->assertDatabaseHas('thought_positions', [
            'thought_id' => $thoughtA->id,
            'space_id' => $space->id,
            'x' => 10,
            'y' => 20,
        ]);

        $this->actingAs($owner)
            ->postJson(route('thoughts.evolve', $thoughtA), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('content');

        $this->actingAs($intruder)
            ->postJson(route('thoughts.evolve', $thoughtA), [
                'content' => 'Denied evolution',
            ])
            ->assertForbidden();

        $evolveResponse = $this->actingAs($owner)
            ->postJson(route('thoughts.evolve', $thoughtA), [
                'content' => 'Evolved endpoint thought',
                'priority' => 'high',
                'tags' => 'growth',
            ])
            ->assertCreated()
            ->assertJsonPath('thought.parent_id', $thoughtA->id);

        $evolvedThoughtId = $evolveResponse->json('thought.id');
        $this->assertDatabaseHas('thoughts', [
            'id' => $evolvedThoughtId,
            'parent_id' => $thoughtA->id,
            'content' => 'Evolved endpoint thought',
        ]);

        $this->actingAs($owner)
            ->postJson(route('thoughts.reviews.store', $thoughtA), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('review_score');

        $this->actingAs($intruder)
            ->postJson(route('thoughts.reviews.store', $thoughtA), [
                'review_score' => 'useful',
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->postJson(route('thoughts.reviews.store', $thoughtA), [
                'review_score' => 'useful',
            ])
            ->assertCreated()
            ->assertJsonPath('review.review_score', 'useful');

        $this->assertDatabaseHas('thought_reviews', [
            'thought_id' => $thoughtA->id,
            'review_score' => 'useful',
        ]);

        $this->actingAs($owner)
            ->postJson(route('spaces.syntheses.store', $space), [
                'content' => 'Too small',
                'thought_ids' => [$thoughtA->id],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('thought_ids');

        $this->actingAs($intruder)
            ->postJson(route('spaces.syntheses.store', $space), [
                'content' => 'Denied synthesis',
                'thought_ids' => [$thoughtA->id, $thoughtB->id],
            ])
            ->assertForbidden();

        $synthesisResponse = $this->actingAs($owner)
            ->postJson(route('spaces.syntheses.store', $space), [
                'content' => 'Synthesized endpoint thought',
                'thought_ids' => [$thoughtA->id, $thoughtB->id],
            ])
            ->assertCreated();

        $synthesizedThoughtId = $synthesisResponse->json('thought.id');
        $this->assertDatabaseHas('thought_syntheses', [
            'synthesized_thought_id' => $synthesizedThoughtId,
            'user_id' => $owner->id,
        ]);
    }

    public function test_lifecycle_mutations_authorize_and_succeed(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $space = $this->createSpaceFor($owner);
        $stream = $this->createStreamFor($space, ['position' => 1]);
        $thought = $this->createThoughtFor($owner, $stream, [
            'content' => 'Lifecycle thought',
            'stage' => 'thought',
            'position' => 1,
        ]);

        $this->actingAs($intruder)
            ->post(route('thoughts.promote', $thought))
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('thoughts.promote', $thought))
            ->assertRedirect(route('spaces.show', $space));

        $this->assertDatabaseHas('thoughts', [
            'id' => $thought->id,
            'stage' => 'concept',
        ]);

        $this->actingAs($intruder)
            ->post(route('thoughts.projects.store', $thought))
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('thoughts.projects.store', $thought))
            ->assertRedirect(route('projects.index'));

        $project = Project::query()->where('thought_id', $thought->id)->firstOrFail();

        $this->actingAs($intruder)
            ->post(route('projects.tasks.store', $project))
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('projects.tasks.store', $project))
            ->assertRedirect(route('projects.index'));

        $task = Task::query()->where('project_id', $project->id)->firstOrFail();

        $this->actingAs($intruder)
            ->patch(route('tasks.complete', $task))
            ->assertForbidden();

        $this->actingAs($owner)
            ->patch(route('tasks.complete', $task))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'done',
        ]);

        $this->actingAs($owner)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }
}

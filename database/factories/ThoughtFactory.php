<?php

namespace Database\Factories;

use App\Domain\Stream\Models\Stream;
use App\Domain\Thought\Models\Thought;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Thought>
 */
class ThoughtFactory extends Factory
{
    protected $model = Thought::class;

    public function definition(): array
    {
        return [
            'stream_id' => Stream::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'content' => fake()->sentence(),
            'stage' => 'thought',
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'tags' => [fake()->word(), fake()->word()],
            'position' => fake()->numberBetween(1, 10),
        ];
    }
}

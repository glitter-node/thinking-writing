<?php

namespace Database\Factories;

use App\Domain\Space\Models\Space;
use App\Domain\Stream\Models\Stream;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stream>
 */
class StreamFactory extends Factory
{
    protected $model = Stream::class;

    public function definition(): array
    {
        return [
            'space_id' => Space::factory(),
            'title' => fake()->words(2, true),
            'position' => fake()->numberBetween(1, 6),
        ];
    }
}

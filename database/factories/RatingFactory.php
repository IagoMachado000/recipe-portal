<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'user_id' => User::factory(),
            'score' => $this->faker->numberBetween(1, 5),
        ];
    }

    public function high(): static
    {
        return $this->state(fn(array $attributes) => [
            'score' => $this->faker->numberBetween(4, 5),
        ]);
    }

    public function medium(): static
    {
        return $this->state(fn(array $attributes) => [
            'score' => 3,
        ]);
    }

    public function low(): static
    {
        return $this->state(fn(array $attributes) => [
            'score' => $this->faker->numberBetween(1, 2),
        ]);
    }

    public function exactScore(int $score): static
    {
        return $this->state(fn(array $attributes) => [
            'score' => $score,
        ]);
    }
}

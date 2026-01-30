<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Place>
 */
class PlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->city(),
            'description' => fake()->paragraph(),
            'address' => fake()->address(),
            'image' => null,
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }

    /**
     * Indicate that the place has an image.
     */
    public function withImage(): static
    {
        return $this->state(fn(array $attributes) => [
            'image' => 'places/test-image.jpg',
        ]);
    }

    /**
     * Indicate specific coordinates for the place.
     */
    public function withCoordinates(float $latitude, float $longitude): static
    {
        return $this->state(fn(array $attributes) => [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\SchoolRegion;
use App\Enums\SchoolType;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' EMEF',
            'type' => SchoolType::Emef,
            'region' => fake()->randomElement(SchoolRegion::cases()),
            'address' => fake()->address(),
            'phone' => fake()->optional()->numerify('(##) ####-####'),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}

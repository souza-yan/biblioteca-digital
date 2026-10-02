<?php

namespace Database\Factories;

use App\Enums\MaterialStatus;
use App\Models\Category;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'category_id' => Category::factory(),
            'type' => 'pdf',
            'author' => fake()->name(),
            'status' => MaterialStatus::DRAFT,
            'published_at' => null,
            'created_by' => User::factory()->staff(),
            'current_version_id' => null,
            'archived_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => MaterialStatus::DRAFT,
            'published_at' => null,
            'archived_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => MaterialStatus::PUBLISHED,
            'published_at' => now(),
            'archived_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => MaterialStatus::ARCHIVED,
            'archived_at' => now(),
        ]);
    }
}

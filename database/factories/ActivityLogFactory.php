<?php

namespace Database\Factories;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
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
            'material_id' => Material::factory(),
            'action' => fake()->randomElement(ActivityAction::cases()),
            'description' => fake()->sentence(),
            'created_at' => now(),
        ];
    }

    public function withoutMaterial(): static
    {
        return $this->state(fn (): array => ['material_id' => null]);
    }
}

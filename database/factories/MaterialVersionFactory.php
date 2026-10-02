<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaterialVersion>
 */
class MaterialVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'material_id' => Material::factory(),
            'version_number' => 1,
            'file_path' => fake()->uuid().'/material.pdf',
            'original_name' => 'material.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1024, 10485760),
            'change_note' => fake()->optional()->sentence(),
            'published_by' => User::factory()->staff(),
        ];
    }
}

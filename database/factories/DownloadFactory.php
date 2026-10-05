<?php

namespace Database\Factories;

use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Download>
 */
class DownloadFactory extends Factory
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
            'material_version_id' => MaterialVersion::factory(),
            'downloaded_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Download $download): void {
            $download->material_id = MaterialVersion::query()
                ->findOrFail($download->material_version_id)
                ->material_id;
        });
    }
}

<?php

use App\Enums\Role;
use App\Models\Material;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;

it('seeds Caraguatatuba schools and initial accounts without duplicates', function () {
    Storage::fake('local');

    $this->seed(DatabaseSeeder::class);
    $this->travelTo(now()->addDay());
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('schools', 16);
    $this->assertDatabaseCount('users', 6);
    $this->assertDatabaseCount('school_user', 5);
    $this->assertDatabaseCount('categories', 7);
    $this->assertDatabaseCount('materials', 16);
    $this->assertDatabaseCount('material_versions', 13);
    $this->assertDatabaseCount('favorites', 3);
    $this->assertDatabaseCount('downloads', 3);
    $this->assertDatabaseCount('activity_logs', 4);

    expect(User::query()->where('role', Role::ADMIN)->count())->toBe(1)
        ->and(User::query()->where('role', Role::STAFF)->count())->toBe(2)
        ->and(User::query()->where('role', Role::TEACHER)->count())->toBe(3);

    expect(User::query()->where('email', 'admin@example.com')->firstOrFail()->schools()->count())->toBe(0);

    $publishedMaterial = Material::query()->where('title', 'Introdução ao Arduino Uno')->firstOrFail();

    expect($publishedMaterial->currentVersion)->not->toBeNull()
        ->and(Storage::disk('local')->exists($publishedMaterial->currentVersion->file_path))->toBeTrue();
});

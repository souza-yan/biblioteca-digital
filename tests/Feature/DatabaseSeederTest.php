<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('seeds the expected records without duplicates when run repeatedly', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('users', 13);
    $this->assertDatabaseCount('categories', 8);
    $this->assertDatabaseCount('materials', 30);

    expect(User::query()->where('role', Role::ADMIN->value)->count())->toBe(1)
        ->and(User::query()->where('role', Role::STAFF->value)->count())->toBe(2)
        ->and(User::query()->where('role', Role::TEACHER->value)->count())->toBe(10);
});

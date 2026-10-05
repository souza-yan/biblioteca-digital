<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

it('seeds the expected records without duplicates when run repeatedly', function () {
    Storage::fake('local');

    $this->seed(DatabaseSeeder::class);
    $this->travelTo(now()->addDay());
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('users', 13);
    $this->assertDatabaseCount('categories', 9);
    $this->assertDatabaseCount('materials', 23);
    $this->assertDatabaseCount('downloads', 3);
    $this->assertDatabaseCount('favorites', 3);
    $this->assertDatabaseCount('activity_logs', 4);

    expect(User::query()->where('role', Role::ADMIN->value)->count())->toBe(1)
        ->and(User::query()->where('role', Role::STAFF->value)->count())->toBe(2)
        ->and(User::query()->where('role', Role::TEACHER->value)->count())->toBe(10);

    expect(DB::table('favorites')
        ->join('users', 'favorites.user_id', '=', 'users.id')
        ->where('users.role', '!=', Role::TEACHER->value)
        ->count())->toBe(0);
});

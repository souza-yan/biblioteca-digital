<?php

use App\Enums\Role;
use App\Models\School;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

test('creates repeatable demonstration schools and role consistent memberships', function () {
    $existingSchool = School::factory()->create(['name' => 'EMEF preexistente']);
    $existingTeacher = User::factory()->teacher()->create([
        'email' => 'existing.teacher@example.com',
    ]);
    $existingTeacher->schools()->attach($existingSchool->getKey(), ['is_active' => true]);

    $seeder = app(DemoDataSeeder::class);

    $seeder->run();
    $seeder->run();

    $this->assertDatabaseCount('schools', 5);
    $this->assertDatabaseCount('users', 25);
    $this->assertDatabaseCount('school_user', 25);

    expect(User::query()->where('role', Role::ADMIN)->count())->toBe(1)
        ->and(User::query()->where('role', Role::STAFF)->count())->toBe(4)
        ->and(User::query()->where('role', Role::TEACHER)->count())->toBe(20);

    $admin = User::query()->where('email', 'demo.admin@example.com')->firstOrFail();

    expect($admin->schools()->wherePivot('is_active', true)->count())->toBe(0)
        ->and(Hash::check('DemoRobotics2026!', $admin->password))->toBeTrue()
        ->and(DB::table('users')->where('email', 'like', 'demo.%@example.com')->count())->toBe(24)
        ->and($existingTeacher->fresh()->belongsToSchool($existingSchool))->toBeTrue();

    $demoSchools = School::query()->where('name', 'like', 'EMEF Demonstração%')->orderBy('name')->get();

    expect($demoSchools)->toHaveCount(4);

    foreach ($demoSchools as $school) {
        expect($school->users()
            ->where('role', Role::STAFF)
            ->wherePivot('is_active', true)
            ->count())->toBe(1);

        expect($school->users()
            ->where('role', Role::TEACHER)
            ->wherePivot('is_active', true)
            ->count())->toBe(5);
    }

    foreach (User::query()->where('role', Role::STAFF)->get() as $staff) {
        expect($staff->schools()->wherePivot('is_active', true)->count())->toBe(1)
            ->and($staff->school())->not->toBeNull();
    }

    expect(User::query()
        ->where('email', 'demo.professor.multiescola@example.com')
        ->firstOrFail()
        ->schools()
        ->wherePivot('is_active', true)
        ->count())->toBe(2);
});

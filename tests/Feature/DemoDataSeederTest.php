<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\CaraguatatubaSchoolsSeeder;
use Illuminate\Support\Facades\Hash;

test('creates repeatable Caraguatatuba school accounts in the requested order', function () {
    $seeder = app(CaraguatatubaSchoolsSeeder::class);

    $seeder->run();
    $seeder->run();

    $this->assertDatabaseCount('schools', 16);
    $this->assertDatabaseCount('users', 6);
    $this->assertDatabaseCount('school_user', 5);

    expect(User::query()->where('role', Role::ADMIN)->count())->toBe(1)
        ->and(User::query()->where('role', Role::STAFF)->count())->toBe(2)
        ->and(User::query()->where('role', Role::TEACHER)->count())->toBe(3);

    $manager = User::query()->where('email', 'gestao2@example.com')->firstOrFail();

    expect($manager->role)->toBe(Role::STAFF)
        ->and($manager->schools()->wherePivot('is_active', true)->count())->toBe(1)
        ->and(Hash::check('password', $manager->password))->toBeTrue();

    expect(User::query()->where('email', 'professor1@example.com')->firstOrFail()->school()?->name)->toBe('EMEF Centro')
        ->and(User::query()->where('email', 'professor2@example.com')->firstOrFail()->school()?->name)->toBe('EMEF Indaiá')
        ->and(User::query()->where('email', 'professor3@example.com')->firstOrFail()->school()?->name)->toBe('EMEF Martim de Sá');
});

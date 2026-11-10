<?php

use App\Enums\Role;
use App\Models\School;
use App\Models\User;
use Database\Seeders\CaraguatatubaSchoolsSeeder;
use Illuminate\Support\Facades\Hash;

test('creates repeatable participating school accounts from the central definitions', function () {
    $seeder = app(CaraguatatubaSchoolsSeeder::class);

    $seeder->run();
    $seeder->run();

    $this->assertDatabaseCount('schools', 4);
    $this->assertDatabaseCount('users', 9);
    $this->assertDatabaseCount('school_user', 8);

    expect(User::query()->where('role', Role::ADMIN)->count())->toBe(1)
        ->and(User::query()->where('role', Role::STAFF)->count())->toBe(4)
        ->and(User::query()->where('role', Role::TEACHER)->count())->toBe(4);

    foreach ([
        'a' => ['mariaaparecida', 'EMEF Profa Maria Aparecida de Carvalho'],
        'b' => ['luizsilvar', 'EMEF Luiz Silvar'],
        'c' => ['mariathereza', 'EMEF Profa Maria Thereza de Souza Castro'],
        'd' => ['luizribeiromuniz', 'EMEF Luiz Ribeiro Muniz'],
    ] as $emailSlug => [$managerSlug, $schoolName]) {
        $manager = User::query()->where('email', "{$managerSlug}@educacaocaraguatatuba.com.br")->firstOrFail();
        $teacher = User::query()->where('email', "professor.{$emailSlug}@educacaocaraguatatuba.com.br")->firstOrFail();

        expect($manager->name)->toBe("Gestão - {$schoolName}")
            ->and($manager->role)->toBe(Role::STAFF)
            ->and($manager->schools()->wherePivot('is_active', true)->count())->toBe(1)
            ->and($manager->school()?->name)->toBe($schoolName)
            ->and(Hash::check(config('accounts.default_password'), $manager->password))->toBeTrue()
            ->and($teacher->name)->toBe("Professor - {$schoolName}")
            ->and($teacher->role)->toBe(Role::TEACHER)
            ->and($teacher->schools()->wherePivot('is_active', true)->count())->toBe(1)
            ->and($teacher->school()?->name)->toBe($schoolName)
            ->and(Hash::check(config('accounts.default_password'), $teacher->password))->toBeTrue();
    }
});

test('removes legacy demonstration schools and accounts', function () {
    $legacySchool = School::factory()->create(['name' => 'EMEF Centro']);
    $legacyUser = User::factory()->staff()->create(['email' => 'gestao1@example.com']);
    $legacyUser->schools()->attach($legacySchool->getKey(), ['is_active' => true]);

    app(CaraguatatubaSchoolsSeeder::class)->run();

    $this->assertDatabaseMissing('users', ['email' => 'gestao1@example.com']);
    $this->assertDatabaseMissing('schools', ['name' => 'EMEF Centro']);
});

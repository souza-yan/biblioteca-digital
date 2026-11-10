<?php

use App\Enums\Role;
use App\Enums\SchoolRegion;
use App\Models\School;
use App\Models\User;
use Database\Seeders\ParticipatingSchoolsSeeder;
use Illuminate\Support\Facades\Hash;

it('creates only the four participating schools and their default managers without duplicates', function () {
    $this->seed(ParticipatingSchoolsSeeder::class);
    $this->seed(ParticipatingSchoolsSeeder::class);

    $this->assertDatabaseCount('schools', 4);
    $this->assertDatabaseCount('users', 4);
    $this->assertDatabaseCount('school_user', 4);
    foreach ([
        'mariaaparecida' => 'EMEF Profa Maria Aparecida de Carvalho',
        'luizsilvar' => 'EMEF Luiz Silvar',
        'mariathereza' => 'EMEF Profa Maria Thereza de Souza Castro',
        'luizribeiromuniz' => 'EMEF Luiz Ribeiro Muniz',
    ] as $slug => $name) {
        $manager = User::query()->where('email', $slug.'@educacaocaraguatatuba.com.br')->firstOrFail();
        expect($manager->role)->toBe(Role::STAFF)
            ->and($manager->must_change_password)->toBeTrue()
            ->and($manager->cpf)->toBeNull()
            ->and($manager->school()->name)->toBe($name)
            ->and($manager->school()->region)->toBe(SchoolRegion::Unspecified)
            ->and(Hash::check('Atividade1!', $manager->password))->toBeTrue();
    }
});

it('preserves customized credentials contact information and disabled accounts on subsequent seeds', function () {
    $this->seed(ParticipatingSchoolsSeeder::class);
    $manager = User::query()->where('email', 'mariaaparecida@educacaocaraguatatuba.com.br')->firstOrFail();
    $manager->forceFill([
        'password' => Hash::make('SenhaPessoal123!'), 'cpf' => '52998224725', 'must_change_password' => false,
        'name' => 'Gestora Maria', 'is_active' => false,
    ])->save();
    $manager->school()->update(['address' => 'Endereço já cadastrado', 'region' => SchoolRegion::North]);
    $password = $manager->password;

    $this->seed(ParticipatingSchoolsSeeder::class);

    expect($manager->fresh()->password)->toBe($password)
        ->and($manager->fresh()->cpf)->toBe('52998224725')
        ->and($manager->fresh()->name)->toBe('Gestora Maria')
        ->and($manager->fresh()->is_active)->toBeFalse()
        ->and($manager->fresh()->must_change_password)->toBeFalse()
        ->and($manager->school()->address)->toBe('Endereço já cadastrado')
        ->and($manager->school()->region)->toBe(SchoolRegion::North);
});

it('renames the matching demonstration school and manager without removing their existing memberships', function () {
    $school = School::factory()->create(['name' => 'Escola A']);
    $manager = User::factory()->staffInSchool($school)->create(['email' => 'a@educacaocaraguatatuba.com.br', 'name' => 'Gestão - Escola A']);
    $teacher = User::factory()->teacher()->create();
    $teacher->schools()->attach($school->getKey(), ['is_active' => true]);
    $cpf = $manager->cpf;

    $this->seed(ParticipatingSchoolsSeeder::class);

    $this->assertDatabaseCount('schools', 4);
    $this->assertDatabaseCount('users', 5);
    expect($school->fresh()->name)->toBe('EMEF Profa Maria Aparecida de Carvalho')
        ->and($manager->fresh()->email)->toBe('mariaaparecida@educacaocaraguatatuba.com.br')
        ->and($manager->fresh()->cpf)->toBe($cpf);
    $this->assertDatabaseHas('school_user', ['school_id' => $school->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => true]);
});

it('rolls back school creation if a manager email belongs to an administrator', function () {
    $admin = User::factory()->admin()->create(['email' => 'mariaaparecida@educacaocaraguatatuba.com.br']);

    expect(fn () => $this->seed(ParticipatingSchoolsSeeder::class))->toThrow(LogicException::class);

    $this->assertDatabaseCount('schools', 0);
    expect($admin->fresh()->role)->toBe(Role::ADMIN);
});

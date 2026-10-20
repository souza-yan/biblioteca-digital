<?php

use App\Actions\School\CreateSchool;
use App\Enums\Role;
use App\Enums\SchoolRegion;
use App\Enums\SchoolType;
use App\Livewire\Schools\SchoolManager;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('allows an administrator to access school management', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('painel.schools'))
        ->assertOk()
        ->assertSee('Escolas')
        ->assertSee('Nova escola');
});

it('blocks staff and teachers from school management', function (User $user) {
    $this->actingAs($user)
        ->get(route('painel.schools'))
        ->assertForbidden();
})->with([
    'staff' => fn () => User::factory()->staffInSchool()->create(),
    'teacher' => fn () => User::factory()->teacher()->create(),
]);

it('creates and updates a school through the administrative interface', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SchoolManager::class)
        ->call('openCreate')
        ->set('form.name', 'EMEF Integração Escolar')
        ->set('form.type', SchoolType::Emef->value)
        ->set('form.region', SchoolRegion::North->value)
        ->set('form.address', 'Rua da Integração, 100')
        ->set('form.phone', '(12) 3456-7890')
        ->call('saveSchool')
        ->assertHasNoErrors()
        ->assertSet('showSchoolForm', false);

    $school = School::query()->where('name', 'EMEF Integração Escolar')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(SchoolManager::class)
        ->call('editSchool', $school->getKey())
        ->set('form.address', 'Avenida Atualizada, 200')
        ->call('saveSchool')
        ->assertHasNoErrors();

    expect($school->fresh()->address)->toBe('Avenida Atualizada, 200');
});

it('creates a staff manager with one active school membership', function () {
    $admin = User::factory()->admin()->create();
    $school = School::factory()->create();

    Livewire::actingAs($admin)
        ->test(SchoolManager::class)
        ->call('openManagerForm', $school->getKey())
        ->set('managerName', 'Gestora de Demonstração')
        ->set('managerEmail', 'gestora.escola@example.com')
        ->call('saveManager')
        ->assertHasNoErrors()
        ->assertSet('showManagerForm', false);

    $manager = User::query()->where('email', 'gestora.escola@example.com')->firstOrFail();

    expect($manager->role)->toBe(Role::STAFF)
        ->and($manager->school()?->is($school))->toBeTrue()
        ->and($manager->schools()->wherePivot('is_active', true)->count())->toBe(1)
        ->and(Hash::check('password', $manager->password))->toBeTrue();
});

it('rejects duplicate school names and manager emails', function () {
    $admin = User::factory()->admin()->create();
    $school = School::factory()->create(['name' => 'EMEF Nome Único']);
    User::factory()->create(['email' => 'gestor.existente@example.com']);

    Livewire::actingAs($admin)
        ->test(SchoolManager::class)
        ->call('openCreate')
        ->set('form.name', $school->name)
        ->set('form.type', SchoolType::Emef->value)
        ->set('form.region', SchoolRegion::Center->value)
        ->set('form.address', 'Rua de Teste')
        ->call('saveSchool')
        ->assertHasErrors(['form.name' => 'unique']);

    Livewire::actingAs($admin)
        ->test(SchoolManager::class)
        ->call('openManagerForm', $school->getKey())
        ->set('managerName', 'Gestor Existente')
        ->set('managerEmail', 'gestor.existente@example.com')
        ->call('saveManager')
        ->assertHasErrors(['managerEmail' => 'unique']);
});

it('prevents direct school creation by non administrators', function () {
    $staff = User::factory()->staffInSchool()->create();

    expect(fn () => app(CreateSchool::class)->handle($staff, [
        'name' => 'EMEF Não Autorizada',
        'type' => SchoolType::Emef,
        'region' => SchoolRegion::South,
        'address' => 'Rua sem autorização',
        'is_active' => true,
    ]))->toThrow(AuthorizationException::class);

    $this->assertDatabaseMissing('schools', ['name' => 'EMEF Não Autorizada']);
});

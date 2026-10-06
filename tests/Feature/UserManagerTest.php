<?php

use App\Livewire\Users\UserManager;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

it('allows staff to create a teacher from the user manager', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff);

    Livewire::test(UserManager::class)
        ->call('openCreate')
        ->set('form.name', 'Ana Professora')
        ->set('form.email', 'ana.professora@example.com')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password')
        ->set('form.role', 'teacher')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'Ana Professora',
        'email' => 'ana.professora@example.com',
        'role' => 'teacher',
    ]);
});

it('allows only admins and staff to create users by policy', function () {
    expect(Gate::forUser(User::factory()->admin()->create())->allows('create', User::class))->toBeTrue()
        ->and(Gate::forUser(User::factory()->staff()->create())->allows('create', User::class))->toBeTrue()
        ->and(Gate::forUser(User::factory()->teacher()->create())->allows('create', User::class))->toBeFalse();
});

it('rejects an admin role in the staff user form', function () {
    $this->actingAs(User::factory()->staff()->create());

    Livewire::test(UserManager::class)
        ->call('openCreate')
        ->set('form.name', 'Conta Administrativa')
        ->set('form.email', 'admin.candidate@example.com')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password')
        ->set('form.role', 'admin')
        ->call('save')
        ->assertHasErrors('form.role');

    $this->assertDatabaseMissing('users', [
        'email' => 'admin.candidate@example.com',
    ]);
});

it('prevents clients from changing the user manager target id to an admin', function (string $property) {
    $staff = User::factory()->staff()->create();
    $teacher = User::factory()->teacher()->create();
    $admin = User::factory()->admin()->create(['name' => 'Administrador intacto']);
    $this->actingAs($staff);

    $component = Livewire::test(UserManager::class)
        ->call('editUser', $teacher->getKey())
        ->assertSet('editingUserId', $teacher->getKey())
        ->assertSet('form.userId', $teacher->getKey());

    expect(fn () => $component->set($property, $admin->getKey()))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    $component->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $admin->getKey(),
        'name' => 'Administrador intacto',
        'role' => 'admin',
    ]);
})->with([
    'component id' => 'editingUserId',
    'form id' => 'form.userId',
]);

it('forbids staff from viewing or editing an admin account', function () {
    $staff = User::factory()->staff()->create();
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($staff);

    Livewire::test(UserManager::class)
        ->assertSee($teacher->name)
        ->assertDontSee($admin->name)
        ->call('editUser', $admin->getKey())
        ->assertForbidden();
});

it('forbids teachers from reaching the user manager route', function () {
    $this->actingAs(User::factory()->teacher()->create())
        ->get('/painel/usuarios')
        ->assertForbidden();
});

it('does not allow an administrator to deactivate their own account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(UserManager::class)
        ->call('toggleActive', $admin->getKey())
        ->assertHasErrors('user');

    $this->assertDatabaseHas('users', [
        'id' => $admin->getKey(),
        'is_active' => true,
    ]);
});

it('filters users by name or email', function () {
    $admin = User::factory()->admin()->create();
    $matchingByName = User::factory()->teacher()->create([
        'name' => 'Busca Pelo Nome',
        'email' => 'first@example.com',
    ]);
    $matchingByEmail = User::factory()->teacher()->create([
        'name' => 'Outro Usuário',
        'email' => 'correspondente@example.com',
    ]);
    $unmatched = User::factory()->teacher()->create([
        'name' => 'Sem Correspondência',
        'email' => 'different@example.com',
    ]);

    $this->actingAs($admin);

    Livewire::test(UserManager::class)
        ->set('search', 'Busca Pelo Nome')
        ->assertSee($matchingByName->name)
        ->assertDontSee($matchingByEmail->email)
        ->set('search', 'correspondente@')
        ->assertSee($matchingByEmail->email)
        ->assertDontSee($matchingByName->email)
        ->assertDontSee($unmatched->email);
});

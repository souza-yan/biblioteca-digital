<?php

use App\Actions\User\UpdateUser;
use App\Enums\Role;
use App\Livewire\Users\UserManager;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

it('forbids teachers from reaching the user manager', function () {
    $this->actingAs(User::factory()->teacher()->create());

    $this->get(route('painel.users'))->assertForbidden();
    Livewire::test(UserManager::class)->assertForbidden();
});

it('prevents staff from creating an admin account', function () {
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

    $this->assertDatabaseMissing('users', ['email' => 'admin.candidate@example.com']);
});

it('allows staff to create a teacher from the user manager', function () {
    $this->actingAs(User::factory()->staff()->create());

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

it('allows administrators to create accounts with assignable roles', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(UserManager::class)
        ->call('openCreate')
        ->set('form.name', 'Novo membro da gestão')
        ->set('form.email', 'nova.gestao@example.com')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password')
        ->set('form.role', 'staff')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'email' => 'nova.gestao@example.com',
        'role' => 'staff',
    ]);
});

it('allows only administrators to change another users role', function () {
    $admin = User::factory()->admin()->create();
    $anotherAdmin = User::factory()->admin()->create();
    $staff = User::factory()->staff()->create();
    $teacher = User::factory()->teacher()->create();

    expect(Gate::forUser($admin)->allows('changeRole', $teacher))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('changeRole', $admin))->toBeFalse()
        ->and(Gate::forUser($staff)->allows('changeRole', $teacher))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('changeRole', $anotherAdmin))->toBeFalse();
});

it('forbids an administrator from changing their own role', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(UserManager::class)
        ->call('editUser', $admin->getKey())
        ->assertSee('Você não pode alterar o seu próprio cargo.')
        ->assertSeeHtml('disabled')
        ->set('form.role', Role::STAFF->value)
        ->call('save')
        ->assertForbidden();

    $this->assertDatabaseHas('users', [
        'id' => $admin->getKey(),
        'role' => Role::ADMIN->value,
    ]);
});

it('allows an administrator to update their own name without changing their role', function () {
    $admin = User::factory()->admin()->create(['name' => 'Nome antigo']);
    $this->actingAs($admin);

    Livewire::test(UserManager::class)
        ->call('editUser', $admin->getKey())
        ->set('form.name', 'Nome atualizado')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $admin->getKey(),
        'name' => 'Nome atualizado',
        'role' => Role::ADMIN->value,
    ]);
});

it('allows an administrator to change a teacher role to staff', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();
    $this->actingAs($admin);

    Livewire::test(UserManager::class)
        ->call('editUser', $teacher->getKey())
        ->set('form.role', Role::STAFF->value)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $teacher->getKey(),
        'role' => Role::STAFF->value,
    ]);
});

it('forbids staff from changing a teacher role', function () {
    $staff = User::factory()->staff()->create();
    $teacher = User::factory()->teacher()->create();
    $this->actingAs($staff);

    Livewire::test(UserManager::class)
        ->call('editUser', $teacher->getKey())
        ->set('form.role', Role::STAFF->value)
        ->call('save')
        ->assertHasErrors('form.role');

    $this->assertDatabaseHas('users', [
        'id' => $teacher->getKey(),
        'role' => Role::TEACHER->value,
    ]);
});

it('prevents updating an administrators own role when the action is called directly', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    expect(fn () => app(UpdateUser::class)->handle($admin, $admin, [
        'role' => Role::STAFF,
    ]))->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('users', [
        'id' => $admin->getKey(),
        'role' => Role::ADMIN->value,
    ]);
});

it('prevents staff from changing roles when the action is called directly', function () {
    $staff = User::factory()->staff()->create();
    $teacher = User::factory()->teacher()->create();
    $this->actingAs($staff);

    expect(fn () => app(UpdateUser::class)->handle($staff, $teacher, [
        'role' => Role::STAFF,
    ]))->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('users', [
        'id' => $teacher->getKey(),
        'role' => Role::TEACHER->value,
    ]);
});

it('prevents staff from viewing or editing an admin account', function () {
    $staff = User::factory()->staff()->create();
    $admin = User::factory()->admin()->create();
    $this->actingAs($staff);

    Livewire::test(UserManager::class)
        ->assertDontSee($admin->name)
        ->call('editUser', $admin->getKey())
        ->assertForbidden();

    $this->assertDatabaseHas('users', [
        'id' => $admin->getKey(),
        'name' => $admin->name,
    ]);
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

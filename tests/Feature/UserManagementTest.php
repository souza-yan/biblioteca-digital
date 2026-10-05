<?php

use App\Livewire\Users\UserManager;
use App\Models\User;
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

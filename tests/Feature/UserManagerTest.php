<?php

use App\Actions\Activity\LogActivity;
use App\Actions\User\SendUserPasswordResetLink;
use App\Enums\ActivityAction;
use App\Livewire\Users\UserManager;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

it('allows staff to create a teacher from the user manager', function () {
    $staff = User::factory()->staffInSchool()->create();

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
        ->and(Gate::forUser(User::factory()->staffInSchool()->create())->allows('create', User::class))->toBeTrue()
        ->and(Gate::forUser(User::factory()->teacher()->create())->allows('create', User::class))->toBeFalse();
});

it('rejects an admin role in the staff user form', function () {
    $this->actingAs(User::factory()->staffInSchool()->create());

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
    $staff = User::factory()->staffInSchool()->create();
    $teacher = User::factory()->teacher()->create();
    $teacher->schools()->attach($staff->school()->getKey(), ['is_active' => true]);
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
    $staff = User::factory()->staffInSchool()->create();
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();
    $teacher->schools()->attach($staff->school()->getKey(), ['is_active' => true]);

    $this->actingAs($staff);

    $component = Livewire::test(UserManager::class)
        ->assertSee($teacher->name)
        ->assertDontSee($admin->name);

    expect(fn () => $component->call('editUser', $admin->getKey()))
        ->toThrow(ModelNotFoundException::class);
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

it('shows at most three global suggestions and allows selecting and clearing an admin user', function () {
    $admin = User::factory()->admin()->create();
    $matches = collect(range(1, 4))->map(fn (int $number): User => User::factory()->teacher()->create([
        'name' => "Suggestion User {$number}",
        'email' => "suggestion{$number}@example.test",
    ]));
    $emailMatch = User::factory()->teacher()->create([
        'name' => 'User Encontrado por E-mail',
        'email' => 'special-address@example.test',
    ]);
    $this->actingAs($admin);

    $component = Livewire::test(UserManager::class)
        ->set('search', 'Suggestion User')
        ->assertViewHas('suggestions', fn ($suggestions): bool => $suggestions->count() === 3)
        ->call('selectUser', $matches->first()->getKey())
        ->assertSet('selectedUserId', $matches->first()->getKey())
        ->assertSee('Selecionado: '.$matches->first()->name)
        ->assertViewHas('users', fn ($users): bool => $users->count() === 1
            && $users->first()->is($matches->first()))
        ->call('clearSelectedUser')
        ->assertSet('selectedUserId', null)
        ->set('search', 'special-address@')
        ->assertViewHas('suggestions', fn ($suggestions): bool => $suggestions->contains(
            fn (User $user): bool => $user->is($emailMatch),
        ));
});

it('limits staff suggestions to local teachers and rejects selecting a foreign user id', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $staff = User::factory()->staffInSchool($schoolA)->create();
    $localTeacher = User::factory()->teacher()->create([
        'name' => 'Suggestion Local Teacher',
        'email' => 'local-suggestion@example.test',
    ]);
    $localTeacher->schools()->attach($schoolA->getKey(), ['is_active' => true]);
    $foreignTeacher = User::factory()->teacher()->create([
        'name' => 'Suggestion Foreign Teacher',
        'email' => 'foreign-suggestion@example.test',
    ]);
    $foreignTeacher->schools()->attach($schoolB->getKey(), ['is_active' => true]);

    $component = Livewire::actingAs($staff)
        ->test(UserManager::class)
        ->set('search', 'Suggestion')
        ->assertViewHas('suggestions', fn ($suggestions): bool => $suggestions->count() === 1
            && $suggestions->first()->is($localTeacher))
        ->assertDontSee($foreignTeacher->email);

    expect(fn () => $component->call('selectUser', $foreignTeacher->getKey()))
        ->toThrow(ModelNotFoundException::class);
});

it('sends an admin initiated password reset through Fortify without changing school links', function () {
    Notification::fake();
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();
    $teacher->schools()->attach([
        $schoolA->getKey() => ['is_active' => true],
        $schoolB->getKey() => ['is_active' => true],
    ]);

    Livewire::actingAs($admin)
        ->test(UserManager::class)
        ->call('sendPasswordResetLink', $teacher->getKey());

    Notification::assertSentTo($teacher, ResetPassword::class);
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $admin->getKey(),
        'action' => ActivityAction::USER_PASSWORD_RESET_REQUESTED->value,
    ]);
    $this->assertDatabaseHas('school_user', [
        'school_id' => $schoolA->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => true,
    ]);
    $this->assertDatabaseHas('school_user', [
        'school_id' => $schoolB->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => true,
    ]);
});

it('returns the successful broker status when activity logging fails after the broker sends the notification', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();
    $logger = Mockery::mock(LogActivity::class);
    $logger->shouldReceive('handle')->once()->andThrow(new RuntimeException('Activity log unavailable.'));
    app()->instance(LogActivity::class, $logger);

    $status = app(SendUserPasswordResetLink::class)->handle($admin, $teacher);

    expect($status)->toBe(Password::RESET_LINK_SENT);
    Notification::assertSentTo($teacher, ResetPassword::class);
});

it('allows admins to update global account fields without changing school links', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create([
        'name' => 'Nome compartilhado',
        'email' => 'shared@example.test',
    ]);
    $teacher->schools()->attach([
        $schoolA->getKey() => ['is_active' => true],
        $schoolB->getKey() => ['is_active' => true],
    ]);
    $this->actingAs($admin);

    Livewire::test(UserManager::class)
        ->call('editUser', $teacher->getKey())
        ->set('form.name', 'Nome atualizado')
        ->set('form.email', 'updated@example.test')
        ->set('form.password', 'new-secret-password')
        ->set('form.password_confirmation', 'new-secret-password')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $teacher->getKey(),
        'name' => 'Nome atualizado',
        'email' => 'updated@example.test',
    ]);
    expect(Hash::check('new-secret-password', $teacher->fresh()->password))->toBeTrue();
    $this->assertDatabaseHas('school_user', [
        'school_id' => $schoolA->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => true,
    ]);
    $this->assertDatabaseHas('school_user', [
        'school_id' => $schoolB->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => true,
    ]);
});

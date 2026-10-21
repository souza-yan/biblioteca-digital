<?php

use App\Actions\User\ResetUserPasswordToDefault;
use App\Enums\ActivityAction;
use App\Livewire\Users\UserManager;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

it('allows staff to create a teacher from the user manager', function () {
    $staff = User::factory()->staffInSchool()->create();

    $this->actingAs($staff);

    Livewire::test(UserManager::class)
        ->call('openCreate')
        ->assertSet('form.role', 'teacher')
        ->assertSee('Novo professor')
        ->assertDontSee('wire:model="form.role"', false)
        ->set('form.name', 'Ana Professora')
        ->set('form.email', 'ana.professora@example.com')
        ->set('form.role', 'teacher')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'Ana Professora',
        'email' => 'ana.professora@example.com',
        'role' => 'teacher',
    ]);
    expect(Hash::check('password', User::query()->where('email', 'ana.professora@example.com')->value('password')))
        ->toBeTrue();
});

it('shows staff only the local teacher identification and membership status action', function () {
    $staff = User::factory()->staffInSchool()->create();
    $teacher = User::factory()->teacher()->create();
    $teacher->schools()->attach($staff->school()->getKey(), ['is_active' => true]);

    Livewire::actingAs($staff)
        ->test(UserManager::class)
        ->assertSee($teacher->name)
        ->assertSee($teacher->email)
        ->assertDontSee('Conta global')
        ->assertDontSee('Vínculo nesta escola')
        ->assertDontSee('Editar usuário '.$teacher->name)
        ->assertSee('Desativar usuário '.$teacher->name);
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
        ->set('form.role', 'admin')
        ->call('save')
        ->assertHasErrors('form.role');

    $this->assertDatabaseMissing('users', [
        'email' => 'admin.candidate@example.com',
    ]);
});

it('shows administrators only teachers and rejects management registration from the professor form', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create(['name' => 'Professora exibida']);
    $manager = User::factory()->staffInSchool()->create(['name' => 'Gestora não exibida']);

    Livewire::actingAs($admin)
        ->test(UserManager::class)
        ->assertSee('Professores')
        ->assertSee($teacher->name)
        ->assertDontSee($manager->name)
        ->call('openCreate')
        ->set('form.name', 'Gestora inválida')
        ->set('form.email', 'gestora.invalida@example.com')
        ->set('form.role', 'staff')
        ->call('save')
        ->assertHasErrors('form.role');

    $this->assertDatabaseMissing('users', ['email' => 'gestora.invalida@example.com']);
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

it('does not allow an administrator account to be managed from the professor screen', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    expect(fn () => Livewire::test(UserManager::class)
        ->call('toggleActive', $admin->getKey()))
        ->toThrow(ModelNotFoundException::class);

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

it('lets an admin reset a password to the default and invalidate stored sessions without changing school links', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create(['password' => Hash::make('senha-anterior')]);
    $teacher->schools()->attach([
        $schoolA->getKey() => ['is_active' => true],
        $schoolB->getKey() => ['is_active' => true],
    ]);

    DB::table('sessions')->insert([
        'id' => 'teacher-active-session',
        'user_id' => $teacher->getKey(),
        'payload' => 'test-payload',
        'last_activity' => now()->timestamp,
    ]);
    config()->set('session.driver', 'database');

    Livewire::actingAs($admin)
        ->test(UserManager::class)
        ->call('openPasswordResetConfirmation', $teacher->getKey())
        ->assertSet('showPasswordResetConfirmation', true)
        ->assertSee('password')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertSet('showPasswordResetConfirmation', false);

    expect(Hash::check('password', $teacher->fresh()->password))->toBeTrue();
    $this->assertDatabaseMissing('sessions', ['id' => 'teacher-active-session']);
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $admin->getKey(),
        'action' => ActivityAction::USER_PASSWORD_RESET_BY_ADMIN->value,
    ]);
    $this->assertDatabaseHas('school_user', [
        'school_id' => $schoolA->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => true,
    ]);
    $this->assertDatabaseHas('school_user', [
        'school_id' => $schoolB->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => true,
    ]);
});

it('refuses a direct password reset by staff', function () {
    $staff = User::factory()->staffInSchool()->create();
    $teacher = User::factory()->teacher()->create();
    $originalPassword = $teacher->password;

    expect(fn () => app(ResetUserPasswordToDefault::class)->handle($staff, $teacher))
        ->toThrow(AuthorizationException::class);
    expect($teacher->fresh()->password)->toBe($originalPassword);
});

it('shows and updates every active school membership for a teacher', function () {
    $admin = User::factory()->admin()->create();
    $schoolA = School::factory()->create(['name' => 'EMEF Escola A']);
    $schoolB = School::factory()->create(['name' => 'EMEF Escola B']);
    $schoolC = School::factory()->create(['name' => 'EMEF Escola C']);
    $teacher = User::factory()->teacher()->create();
    $teacher->schools()->attach([
        $schoolA->getKey() => ['is_active' => true],
        $schoolB->getKey() => ['is_active' => true],
    ]);

    Livewire::actingAs($admin)
        ->test(UserManager::class)
        ->call('editUser', $teacher->getKey())
        ->assertSet('form.school_ids', fn (array $ids): bool => collect($ids)->sort()->values()->all() === collect([
            $schoolA->getKey(),
            $schoolB->getKey(),
        ])->sort()->values()->all())
        ->assertViewHas('schoolOptions', fn (array $options): bool => collect($options)->pluck('label')->all() === [
            $schoolA->name, $schoolB->name, $schoolC->name,
        ])
        ->set('form.school_ids', [$schoolB->getKey(), $schoolC->getKey()])
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('school_user', [
        'school_id' => $schoolA->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => false,
    ]);
    $this->assertDatabaseHas('school_user', [
        'school_id' => $schoolB->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => true,
    ]);
    $this->assertDatabaseHas('school_user', [
        'school_id' => $schoolC->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => true,
    ]);
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

it('creates a teacher with two schools using the additional selector', function () {
    $schools = School::factory()->count(2)->create();

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(UserManager::class)
        ->call('openCreate')
        ->set('form.role', 'teacher')
        ->set('form.name', 'Professor de duas escolas')
        ->set('form.email', 'duas.escolas@example.com')
        ->set('form.school_ids.0', $schools[0]->id)
        ->call('addSchool')
        ->assertSee('Outra escola')
        ->assertDontSee('Adicionar outra escola')
        ->set('form.school_ids.1', $schools[1]->id)
        ->call('save')
        ->assertHasNoErrors();

    $teacher = User::where('email', 'duas.escolas@example.com')->firstOrFail();
    expect($teacher->schools()->pluck('schools.id')->sort()->values()->all())
        ->toBe($schools->pluck('id')->sort()->values()->all());
});

it('saves only the selected school when the second selector is empty or removed', function (bool $remove) {
    $school = School::factory()->create();
    $component = Livewire::actingAs(User::factory()->admin()->create())
        ->test(UserManager::class)
        ->call('openCreate')
        ->set('form.role', 'teacher')
        ->set('form.name', 'Professor de uma escola')
        ->set('form.email', 'uma.escola@example.com')
        ->set('form.school_ids.0', $school->id)
        ->call('addSchool');

    if ($remove) {
        $component->set('form.school_ids.1', School::factory()->create()->id)
            ->call('removeSchool', 1)
            ->assertDontSee('Outra escola')
            ->assertSee('Adicionar outra escola');
    }

    $component->call('save')->assertHasNoErrors();

    expect(User::where('email', 'uma.escola@example.com')->firstOrFail()->schools()->pluck('schools.id')->all())
        ->toBe([$school->id]);
})->with(['empty' => false, 'removed' => true]);

it('rejects duplicate schools in the teacher selectors', function () {
    $school = School::factory()->create();

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(UserManager::class)
        ->call('openCreate')
        ->set('form.role', 'teacher')
        ->set('form.name', 'Professor')
        ->set('form.email', 'duplicada@example.com')
        ->set('form.school_ids.0', $school->id)
        ->call('addSchool')
        ->set('form.school_ids.1', $school->id)
        ->call('save')
        ->assertHasErrors(['form.school_ids.0' => 'distinct', 'form.school_ids.1' => 'distinct']);

    $this->assertDatabaseMissing('users', ['email' => 'duplicada@example.com']);
});

it('prevents staff from managing teacher school selectors', function (string $method, array $arguments) {
    Livewire::actingAs(User::factory()->staffInSchool()->create())
        ->test(UserManager::class)
        ->call('openCreate')
        ->call($method, ...$arguments)
        ->assertForbidden();
})->with([
    'add' => ['addSchool', []],
    'remove' => ['removeSchool', [1]],
]);

it('excludes schools selected in other fields and restores them when changed or removed', function () {
    $schoolA = School::factory()->create(['name' => 'Escola A']);
    $schoolB = School::factory()->create(['name' => 'Escola B']);
    $schoolC = School::factory()->create(['name' => 'Escola C']);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(UserManager::class)
        ->call('openCreate')
        ->set('form.role', 'teacher')
        ->set('form.school_ids.0', (string) $schoolA->id)
        ->call('addSchool')
        ->assertViewHas('teacherSchoolOptions', fn (array $options): bool => array_column($options[1], 'value') === [$schoolB->id, $schoolC->id])
        ->set('form.school_ids.1', $schoolB->id)
        ->assertViewHas('teacherSchoolOptions', fn (array $options): bool => array_column($options[0], 'value') === [$schoolA->id, $schoolC->id])
        ->set('form.school_ids.0', $schoolC->id)
        ->assertViewHas('teacherSchoolOptions', fn (array $options): bool => array_column($options[1], 'value') === [$schoolA->id, $schoolB->id])
        ->set('form.school_ids.0', null)
        ->assertViewHas('teacherSchoolOptions', fn (array $options): bool => array_column($options[1], 'value') === [$schoolA->id, $schoolB->id, $schoolC->id])
        ->call('removeSchool', 1)
        ->assertViewHas('teacherSchoolOptions', fn (array $options): bool => array_column($options[0], 'value') === [$schoolA->id, $schoolB->id, $schoolC->id]);
});

<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\User\CreateUser;
use App\Actions\User\UpdateUser;
use App\Enums\Role;
use App\Livewire\Schools\SchoolManager;
use App\Livewire\Users\UserManager;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

it('requires a valid CPF when management creates a teacher', function (string $cpf, string $error) {
    $staff = User::factory()->staffInSchool()->create();

    Livewire::actingAs($staff)->test(UserManager::class)->call('openCreate')
        ->set('form.name', 'Professora Ana')->set('form.email', 'ana@example.com')->set('form.cpf', $cpf)
        ->call('save')->assertHasErrors('form.cpf')->assertSee($error);

    $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
})->with([
    ['', 'O CPF é obrigatório.'], ['11111111111', 'Informe um CPF válido.'],
    ['52998224724', 'Informe um CPF válido.'], ['5299822472', 'Informe um CPF válido.'],
    ['abc52998224725', 'Informe um CPF válido.'],
]);

it('normalizes and stores a CPF without exposing it in user serialization', function () {
    $staff = User::factory()->staffInSchool()->create();

    Livewire::actingAs($staff)->test(UserManager::class)->call('openCreate')
        ->set('form.name', 'Professora Ana')->set('form.email', 'ana@example.com')->set('form.cpf', '012.345.678-90')
        ->call('save')->assertHasNoErrors();

    $teacher = User::query()->where('email', 'ana@example.com')->firstOrFail();
    expect($teacher->cpf)->toBe('01234567890');
    expect($teacher->toArray())->not->toHaveKey('cpf');
    $this->assertDatabaseHas('school_user', ['user_id' => $teacher->getKey(), 'school_id' => $staff->school()->getKey(), 'is_active' => true]);
});

it('rejects an already registered CPF even from a different school', function () {
    $staff = User::factory()->staffInSchool()->create();
    User::factory()->staffInSchool()->create(['cpf' => '52998224725']);

    Livewire::actingAs($staff)->test(UserManager::class)->call('openCreate')
        ->set('form.name', 'Professora Ana')->set('form.email', 'ana@example.com')->set('form.cpf', '529.982.247-25')
        ->call('save')->assertHasErrors(['form.cpf' => 'unique']);

    $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
});

it('rejects creating teachers and management without CPF directly through the backend action', function (Role $role) {
    $admin = User::factory()->admin()->create();
    $school = School::factory()->create();

    expect(fn () => app(CreateUser::class)->handle($admin, [
        'name' => 'Professora Ana', 'email' => 'ana@example.com', 'role' => $role, 'school_id' => $school->getKey(),
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
})->with([Role::TEACHER, Role::STAFF]);

it('lets administrators create another administrator without a CPF', function () {
    $admin = User::factory()->admin()->create();

    $created = app(CreateUser::class)->handle($admin, [
        'name' => 'Administrador', 'email' => 'admin.novo@example.com', 'role' => Role::ADMIN,
    ]);

    expect($created->cpf)->toBeNull();
    $this->assertModelExists($created);
});

it('rejects duplicate CPF through backend actions and preserves existing accounts', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->teacher()->create(['cpf' => '52998224725']);
    $target = User::factory()->teacher()->create();
    $originalCpf = $target->cpf;

    expect(fn () => app(CreateUser::class)->handle($admin, [
        'name' => 'Professora Ana', 'email' => 'ana@example.com', 'role' => Role::TEACHER, 'cpf' => '529.982.247-25',
    ]))->toThrow(ValidationException::class);
    expect(fn () => app(UpdateUser::class)->handle($admin, $target, ['cpf' => '529.982.247-25']))
        ->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
    expect($target->fresh()->cpf)->toBe($originalCpf);
});

it('requires a CPF when an administrator registers a school manager', function () {
    $admin = User::factory()->admin()->create();
    $school = School::factory()->create();

    $component = Livewire::actingAs($admin)->test(SchoolManager::class)->call('openManagerForm', $school->getKey())
        ->set('managerName', 'Gestora Ana')->set('managerEmail', 'gestora@example.com');
    $component->call('saveManager')->assertHasErrors(['managerCpf' => 'required']);
    $this->assertDatabaseMissing('users', ['email' => 'gestora@example.com']);

    $component->set('managerCpf', '529.982.247-25')->call('saveManager')->assertHasNoErrors();

    $this->assertDatabaseHas('users', ['email' => 'gestora@example.com', 'cpf' => '52998224725', 'role' => Role::STAFF->value]);
});

it('lets administrators regularize a legacy teacher and keep the same CPF on later edits', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->withoutCpf()->create();

    $component = Livewire::actingAs($admin)->test(UserManager::class)->call('editUser', $teacher->getKey())
        ->set('form.cpf', '012.345.678-90')->call('save')->assertHasNoErrors();
    $component->call('editUser', $teacher->getKey())->call('save')->assertHasNoErrors();

    expect($teacher->fresh()->cpf)->toBe('01234567890');
});

it('lets administrators regularize a legacy manager without changing its membership', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->staffInSchool()->withoutCpf()->create();
    $school = $manager->school();

    Livewire::actingAs($admin)->test(SchoolManager::class)->call('editManager', $school->getKey(), $manager->getKey())
        ->set('managerCpf', '529.982.247-25')->call('saveManager')
        ->assertHasNoErrors()->assertSet('showManagerForm', false);

    expect($manager->fresh()->cpf)->toBe('52998224725');
    $this->assertDatabaseHas('school_user', ['user_id' => $manager->getKey(), 'school_id' => $school->getKey(), 'is_active' => true]);
});

it('does not let management change a teacher CPF through crafted form state or the backend action', function () {
    $staff = User::factory()->staffInSchool()->create();
    $teacher = User::factory()->teacher()->create();
    $teacher->schools()->attach($staff->school()->getKey(), ['is_active' => true]);
    $originalCpf = $teacher->cpf;

    Livewire::actingAs($staff)->test(UserManager::class)->call('editUser', $teacher->getKey())
        ->assertSet('form.cpf', '')->set('form.cpf', '52998224725')->call('save')->assertHasNoErrors();
    expect(fn () => app(UpdateUser::class)->handle($staff, $teacher, ['cpf' => '52998224725']))
        ->toThrow(AuthorizationException::class);

    expect($teacher->fresh()->cpf)->toBe($originalCpf);
});

it('prevents a profile update from changing the account CPF', function () {
    $teacher = User::factory()->teacher()->create();
    $originalCpf = $teacher->cpf;

    $this->actingAs($teacher);
    app(UpdateUserProfileInformation::class)->update($teacher, [
        'name' => $teacher->name, 'email' => $teacher->email, 'cpf' => '52998224725',
    ]);

    expect($teacher->fresh()->cpf)->toBe($originalCpf);
});

it('restricts CPF correction to active administrators and non administrator targets', function () {
    $admin = User::factory()->admin()->create();
    $inactiveAdmin = User::factory()->admin()->inactive()->create();
    $staff = User::factory()->staffInSchool()->create();
    $teacher = User::factory()->teacher()->create();

    expect(Gate::forUser($admin)->allows('updateCpf', $teacher))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('updateCpf', $staff))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('updateCpf', $admin))->toBeFalse()
        ->and(Gate::forUser($inactiveAdmin)->allows('updateCpf', $teacher))->toBeFalse()
        ->and(Gate::forUser($staff)->allows('updateCpf', $teacher))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('updateCpf', $teacher))->toBeFalse();
});

it('reports missing CPFs before enabling mandatory CPF login without printing identifiers', function () {
    $teacher = User::factory()->teacher()->withoutCpf()->create();
    User::factory()->admin()->create();

    $this->artisan('accounts:cpf-status')->expectsOutput('Contas sem CPF: 1')->assertFailed();

    $teacher->update(['cpf' => '52998224725']);
    $this->artisan('accounts:cpf-status')->expectsOutput('Contas sem CPF: 0')->assertSuccessful();
});

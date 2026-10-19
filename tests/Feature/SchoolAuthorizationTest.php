<?php

use App\Actions\Category\CreateCategory;
use App\Actions\Dashboard\BuildDashboard;
use App\Actions\Material\CreateMaterial;
use App\Actions\User\CreateUser;
use App\Actions\User\SendUserPasswordResetLink;
use App\Actions\User\ToggleUserActive;
use App\Actions\User\UpdateUser;
use App\Enums\ActivityAction;
use App\Enums\Role;
use App\Livewire\Activity\ActivityLogIndex;
use App\Livewire\Users\UserManager;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('requires exactly one active school for staff administration but keeps profile and logout available', function () {
    $staff = User::factory()->staff()->create();
    $this->actingAs($staff);

    $this->get('/painel/usuarios')->assertForbidden();
    $this->get('/user/profile')->assertOk();
    $this->post('/logout')->assertRedirect('/');
});

it('limits staff user management to teachers linked to its school', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $staff = User::factory()->staffInSchool($schoolA)->create();
    $localTeacher = User::factory()->teacher()->create();
    $localTeacher->schools()->attach($schoolA->getKey(), ['is_active' => true]);
    $foreignTeacher = User::factory()->teacher()->create();
    $foreignTeacher->schools()->attach($schoolB->getKey(), ['is_active' => true]);

    $this->actingAs($staff);

    $component = Livewire::test(UserManager::class)
        ->assertSee($localTeacher->name)
        ->assertDontSee($foreignTeacher->name);

    expect(fn () => $component->call('editUser', $foreignTeacher->getKey()))
        ->toThrow(ModelNotFoundException::class);
});

it('lets staff deactivate a local teacher membership without changing the global account or other schools', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $staff = User::factory()->staffInSchool($schoolA)->create();
    $teacher = User::factory()->teacher()->create();
    $teacher->schools()->attach($schoolA->getKey(), ['is_active' => true]);
    $teacher->schools()->attach($schoolB->getKey(), ['is_active' => true]);

    app(ToggleUserActive::class)->handle($staff, $teacher);

    $this->assertDatabaseHas('users', ['id' => $teacher->getKey(), 'is_active' => true]);
    $this->assertDatabaseHas('school_user', ['school_id' => $schoolA->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => false]);
    $this->assertDatabaseHas('school_user', ['school_id' => $schoolB->getKey(), 'user_id' => $teacher->getKey(), 'is_active' => true]);
});

it('does not allow staff to write materials or categories through direct actions', function () {
    Storage::fake('local');
    $staff = User::factory()->staffInSchool()->create();

    expect(fn () => app(CreateMaterial::class)->handle($staff, [
        'title' => 'Tentativa',
        'description' => 'Teste',
        'category_id' => Category::factory()->create()->getKey(),
        'type' => 'Apostila',
        'author' => 'Equipe',
    ]))->toThrow(AuthorizationException::class);

    expect(fn () => app(CreateCategory::class)->handle($staff, [
        'name' => 'Tentativa', 'slug' => 'tentativa', 'description' => null, 'is_active' => true,
    ]))->toThrow(AuthorizationException::class);

    expect(Gate::forUser($staff)->allows('create', Material::class))->toBeFalse()
        ->and(Gate::forUser($staff)->allows('create', Category::class))->toBeFalse();
});

it('keeps school context methods correct when relations are already loaded', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $user = User::factory()->teacher()->create();
    $user->schools()->attach($schoolA->getKey(), ['is_active' => true]);
    $user->load('schools');

    expect($user->school()?->is($schoolA))->toBeTrue()
        ->and($user->belongsToSchool($schoolA))->toBeTrue()
        ->and($user->isExclusiveTo($schoolA))->toBeTrue();

    $user->schools()->attach($schoolB->getKey(), ['is_active' => false]);
    expect($user->isExclusiveTo($schoolA))->toBeFalse();
});

it('blocks staff without a valid school from dashboards and administrative endpoints', function () {
    $staff = User::factory()->staff()->create();
    $this->actingAs($staff);

    $this->get('/')->assertForbidden();
    $this->get('/painel/atividades')->assertForbidden();
    $this->get('/painel/materiais-mais-baixados')->assertForbidden();
});

it('does not grant teachers administrative material write abilities', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->create();

    expect(Gate::forUser($teacher)->allows('create', Material::class))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('update', $material))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('archive', $material))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('publish', $material))->toBeFalse();
});

it('prevents staff from creating an administrator through the direct action', function () {
    $staff = User::factory()->staffInSchool()->create();

    expect(fn () => app(CreateUser::class)->handle($staff, [
        'name' => 'Escalação de cargo',
        'email' => 'staff-escalation@example.test',
        'password' => 'password',
        'role' => Role::ADMIN,
    ]))->toThrow(AuthorizationException::class);

    $this->assertDatabaseMissing('users', ['email' => 'staff-escalation@example.test']);
});

it('filters activity logs to users currently linked to the staff school', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $staff = User::factory()->staffInSchool($schoolA)->create();
    $localTeacher = User::factory()->teacher()->create();
    $localTeacher->schools()->attach($schoolA->getKey(), ['is_active' => true]);
    $foreignTeacher = User::factory()->teacher()->create();
    $foreignTeacher->schools()->attach($schoolB->getKey(), ['is_active' => true]);
    ActivityLog::factory()->withoutMaterial()->create([
        'user_id' => $localTeacher->getKey(),
        'action' => ActivityAction::USER_UPDATED,
        'description' => 'Registro da escola local',
    ]);
    ActivityLog::factory()->withoutMaterial()->create([
        'user_id' => $foreignTeacher->getKey(),
        'action' => ActivityAction::USER_UPDATED,
        'description' => 'Registro da escola externa',
    ]);

    Livewire::actingAs($staff)
        ->test(ActivityLogIndex::class)
        ->call('selectTab', 'changes')
        ->assertSee('Registro da escola local')
        ->assertDontSee('Registro da escola externa');

    Livewire::actingAs($staff)
        ->test(ActivityLogIndex::class)
        ->set('changesUserFilter', (string) $foreignTeacher->getKey())
        ->assertHasErrors('changesUserFilter')
        ->call('selectTab', 'changes')
        ->assertSee('Registro da escola local')
        ->assertDontSee('Registro da escola externa');
});

it('deactivates school links when a linked user is promoted to administrator', function () {
    $school = School::factory()->create();
    $admin = User::factory()->admin()->create();
    $target = User::factory()->teacher()->create();
    $target->schools()->attach($school->getKey(), ['is_active' => true]);
    $staff = User::factory()->staffInSchool($school)->create();
    ActivityLog::factory()->withoutMaterial()->create([
        'user_id' => $target->getKey(),
        'action' => ActivityAction::USER_UPDATED,
        'description' => 'Atividade anterior à promoção',
    ]);

    app(UpdateUser::class)->handle($admin, $target, ['role' => Role::ADMIN->value]);

    $this->assertDatabaseHas('school_user', [
        'school_id' => $school->getKey(),
        'user_id' => $target->getKey(),
        'is_active' => false,
    ]);

    Livewire::actingAs($staff)
        ->test(ActivityLogIndex::class)
        ->call('selectTab', 'changes')
        ->assertDontSee('Atividade anterior à promoção');

    Livewire::actingAs($admin)
        ->test(ActivityLogIndex::class)
        ->call('selectTab', 'changes')
        ->assertSee('Atividade anterior à promoção');
});

it('does not allow staff actions to change global user account attributes', function (array $attributes) {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $staff = User::factory()->staffInSchool($schoolA)->create();
    $teacher = User::factory()->teacher()->create([
        'name' => 'Nome original',
        'email' => 'original@example.test',
    ]);
    $teacher->schools()->attach([
        $schoolA->getKey() => ['is_active' => true],
        $schoolB->getKey() => ['is_active' => true],
    ]);

    expect(fn () => app(UpdateUser::class)->handle($staff, $teacher, $attributes))
        ->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('users', [
        'id' => $teacher->getKey(),
        'name' => 'Nome original',
        'email' => 'original@example.test',
    ]);
    $this->assertDatabaseHas('school_user', [
        'school_id' => $schoolA->getKey(),
        'user_id' => $teacher->getKey(),
        'is_active' => true,
    ]);
    $this->assertDatabaseHas('school_user', [
        'school_id' => $schoolB->getKey(),
        'user_id' => $teacher->getKey(),
        'is_active' => true,
    ]);
})->with([
    'name' => [['name' => 'Nome alterado']],
    'email' => [['email' => 'alterado@example.test']],
    'password' => [['password' => 'new-secret-password']],
]);

it('keeps global account fields and all school links unchanged when staff tampers with the livewire form', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $staff = User::factory()->staffInSchool($schoolA)->create();
    $teacher = User::factory()->teacher()->create([
        'name' => 'Nome original',
        'email' => 'original@example.test',
    ]);
    $teacher->schools()->attach([
        $schoolA->getKey() => ['is_active' => true],
        $schoolB->getKey() => ['is_active' => true],
    ]);

    Livewire::actingAs($staff)
        ->test(UserManager::class)
        ->call('editUser', $teacher->getKey())
        ->set('form.name', 'Nome manipulado')
        ->set('form.email', 'manipulado@example.test')
        ->set('form.password', 'new-secret-password')
        ->set('form.password_confirmation', 'new-secret-password')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $teacher->getKey(),
        'name' => 'Nome original',
        'email' => 'original@example.test',
    ]);
    $this->assertDatabaseMissing('school_user', [
        'school_id' => $schoolB->getKey(),
        'user_id' => $teacher->getKey(),
        'is_active' => false,
    ]);
});

it('does not let staff initiate a global password reset for a local teacher', function () {
    Notification::fake();
    $school = School::factory()->create();
    $staff = User::factory()->staffInSchool($school)->create();
    $teacher = User::factory()->teacher()->create();
    $teacher->schools()->attach($school->getKey(), ['is_active' => true]);

    expect(fn () => app(SendUserPasswordResetLink::class)->handle($staff, $teacher))
        ->toThrow(AuthorizationException::class);

    expect(Gate::forUser($staff)->allows('resetPassword', $teacher))->toBeFalse();
    Livewire::actingAs($staff)
        ->test(UserManager::class)
        ->call('sendPasswordResetLink', $teacher->getKey())
        ->assertForbidden();

    Notification::assertNothingSent();
});

it('scopes staff dashboard download aggregates to its current school and keeps admin totals global', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $staff = User::factory()->staffInSchool($schoolA)->create();
    $localTeacher = User::factory()->teacher()->create();
    $localTeacher->schools()->attach($schoolA->getKey(), ['is_active' => true]);
    $foreignTeacher = User::factory()->teacher()->create();
    $foreignTeacher->schools()->attach($schoolB->getKey(), ['is_active' => true]);
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create();

    Download::factory()->create([
        'user_id' => $localTeacher->getKey(),
        'material_id' => $material->getKey(),
        'material_version_id' => $version->getKey(),
    ]);
    Download::factory()->create([
        'user_id' => $foreignTeacher->getKey(),
        'material_id' => $material->getKey(),
        'material_version_id' => $version->getKey(),
    ]);

    $staffDashboard = app(BuildDashboard::class)->handle($staff);
    $adminDashboard = app(BuildDashboard::class)->handle(User::factory()->admin()->create());

    expect($staffDashboard['recentDownloadsCount'])->toBe(1)
        ->and($staffDashboard['activeUsersCount'])->toBe(2)
        ->and($staffDashboard['topDownloadedMaterials']->first()->downloads_count)->toBe(1)
        ->and($adminDashboard['recentDownloadsCount'])->toBe(2)
        ->and($adminDashboard['topDownloadedMaterials']->first()->downloads_count)->toBe(2);
});

it('does not let administrators deactivate another administrator', function () {
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();

    expect(fn () => app(ToggleUserActive::class)->handle($admin, $otherAdmin))
        ->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('users', [
        'id' => $otherAdmin->getKey(),
        'is_active' => true,
    ]);
});

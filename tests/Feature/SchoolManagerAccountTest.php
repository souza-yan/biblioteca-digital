<?php

use App\Enums\ActivityAction;
use App\Enums\Role;
use App\Livewire\Schools\SchoolManager;
use App\Models\ActivityLog;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Exceptions\PublicPropertyNotFoundException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

it('updates manager identity without replacing their password photo or role', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->staffInSchool()->create(['profile_photo_path' => 'profile-photos/manager.jpg']);
    $passwordHash = $manager->password;

    Livewire::actingAs($admin)->test(SchoolManager::class)
        ->call('editManager', $manager->school()->id, $manager->id)
        ->assertSet('managerName', $manager->name)
        ->set('managerName', 'Gestora Atualizada')
        ->set('managerEmail', 'gestora.atualizada@example.com')
        ->set('managerCpf', '529.982.247-25')
        ->call('saveManager')->assertHasNoErrors()
        ->assertSet('showManagerForm', false)
        ->assertSet('editingManagerId', null)
        ->assertDispatched('ts-ui:toast');

    expect($manager->fresh())->name->toBe('Gestora Atualizada')
        ->email->toBe('gestora.atualizada@example.com')->cpf->toBe('52998224725')
        ->password->toBe($passwordHash)->profile_photo_path->toBe('profile-photos/manager.jpg')
        ->role->toBe(Role::STAFF);
});

it('moves a manager to one other school while preserving old membership history', function () {
    $admin = User::factory()->admin()->create();
    $source = School::factory()->create();
    $destination = School::factory()->create();
    $manager = User::factory()->staffInSchool($source)->create();

    Livewire::actingAs($admin)->test(SchoolManager::class)
        ->call('editManager', $source->id, $manager->id)
        ->set('managerDestinationSchoolId', $destination->id)
        ->call('saveManager')->assertHasNoErrors();

    $this->assertDatabaseHas('school_user', ['user_id' => $manager->id, 'school_id' => $source->id, 'is_active' => false]);
    $this->assertDatabaseHas('school_user', ['user_id' => $manager->id, 'school_id' => $destination->id, 'is_active' => true]);
    expect($manager->fresh()->schools()->wherePivot('is_active', true)->count())->toBe(1);
});

it('rejects duplicate manager identifiers without changing the account', function (string $field) {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->teacher()->create();
    $manager = User::factory()->staffInSchool()->create();
    $original = $manager->fresh()->getAttributes();

    Livewire::actingAs($admin)->test(SchoolManager::class)
        ->call('editManager', $manager->school()->id, $manager->id)
        ->set($field, $field === 'managerEmail' ? $other->email : $other->cpf)
        ->call('saveManager')->assertHasErrors($field)
        ->assertSet('showManagerForm', true)
        ->assertDispatched('ts-ui:toast');

    expect($manager->fresh()->getAttributes())->toBe($original);
})->with(['managerEmail', 'managerCpf']);

it('shows only the dedicated reset option instead of password fields in manager editing', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->staffInSchool()->create();

    Livewire::actingAs($admin)->test(SchoolManager::class)
        ->call('editManager', $manager->school()->id, $manager->id)
        ->assertDontSee('Nova senha (opcional)')
        ->assertDontSee('Confirmar nova senha')
        ->assertSee('Redefinir senha');
});

it('rejects attempts to set password properties through manager editing', function (string $property) {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->staffInSchool()->create(['password' => Hash::make('SenhaAtual123!')]);
    $originalHash = $manager->password;

    $component = Livewire::actingAs($admin)->test(SchoolManager::class)
        ->call('editManager', $manager->school()->id, $manager->id);

    expect(fn () => $component->set($property, 'NovaSenha123!'))->toThrow(PublicPropertyNotFoundException::class);

    expect($manager->fresh()->password)->toBe($originalHash);
})->with(['managerPassword', 'managerPasswordConfirmation']);

it('rejects a missing destination school without changing the current membership', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->staffInSchool()->create();
    $school = $manager->school();

    Livewire::actingAs($admin)->test(SchoolManager::class)
        ->call('editManager', $school->id, $manager->id)
        ->set('managerDestinationSchoolId', null)
        ->call('saveManager')->assertHasErrors('managerDestinationSchoolId');

    expect($manager->fresh()->school()->id)->toBe($school->id);
});

it('does not reset a password until confirmed and allows cancellation', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->staffInSchool()->create(['password' => Hash::make('SenhaAtual123!')]);
    $originalHash = $manager->password;

    Livewire::actingAs($admin)->test(SchoolManager::class)
        ->call('openManagerPasswordResetConfirmation', $manager->school()->id, $manager->id)
        ->assertSet('showManagerPasswordResetConfirmation', true)
        ->call('closeManagerPasswordResetConfirmation')
        ->assertSet('passwordResetManagerId', null)
        ->assertSet('showManagerPasswordResetConfirmation', false);

    expect($manager->fresh()->password)->toBe($originalHash);
});

it('resets a manager password and revokes only their sessions', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->staffInSchool()->create(['password' => Hash::make('SenhaAtual123!'), 'remember_token' => 'old-token']);
    DB::table('sessions')->insert([
        ['id' => 'manager-session', 'user_id' => $manager->id, 'payload' => '', 'last_activity' => time()],
        ['id' => 'admin-session', 'user_id' => $admin->id, 'payload' => '', 'last_activity' => time()],
    ]);
    $component = Livewire::actingAs($admin)->test(SchoolManager::class)
        ->call('openManagerPasswordResetConfirmation', $manager->school()->id, $manager->id);
    config()->set('session.driver', 'database');

    $component->call('resetManagerPassword')->assertHasNoErrors()
        ->assertSet('showManagerPasswordResetConfirmation', false)->assertDispatched('ts-ui:toast');

    expect(Hash::check(config('accounts.default_password'), $manager->fresh()->password))->toBeTrue()
        ->and($manager->fresh()->must_change_password)->toBeTrue()
        ->and($manager->fresh()->remember_token)->not->toBe('old-token');
    $this->assertDatabaseMissing('sessions', ['id' => 'manager-session']);
    $this->assertDatabaseHas('sessions', ['id' => 'admin-session']);
    expect(ActivityLog::query()->where('action', ActivityAction::USER_PASSWORD_RESET_BY_ADMIN)->exists())->toBeTrue();
});

it('rejects a manager outside the supplied school', function (string $method) {
    $admin = User::factory()->admin()->create();
    $school = School::factory()->create();
    $manager = User::factory()->staffInSchool()->create();
    $component = Livewire::actingAs($admin)->test(SchoolManager::class);

    expect(fn () => $component->call($method, $school->id, $manager->id))->toThrow(ModelNotFoundException::class);
})->with(['editManager', 'openManagerPasswordResetConfirmation']);

it('locks manager identities against client changes', function (string $property) {
    $admin = User::factory()->admin()->create();
    $component = Livewire::actingAs($admin)->test(SchoolManager::class);

    expect(fn () => $component->set($property, 999))->toThrow(CannotUpdateLockedPropertyException::class);
})->with(['editingManagerId', 'managerSchoolId', 'passwordResetManagerId', 'passwordResetManagerSchoolId']);

it('rechecks administrator access before saving or resetting a manager', function (string $method) {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->staffInSchool()->create();
    $component = Livewire::actingAs($admin)->test(SchoolManager::class)
        ->call('editManager', $manager->school()->id, $manager->id)
        ->call('openManagerPasswordResetConfirmation', $manager->school()->id, $manager->id);
    $admin->update(['is_active' => false]);

    $component->call($method)->assertForbidden();
})->with(['saveManager', 'resetManagerPassword']);

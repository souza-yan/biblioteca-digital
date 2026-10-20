<?php

use App\Actions\User\ResetOwnPasswordToDefault;
use App\Enums\ActivityAction;
use App\Enums\Role;
use App\Livewire\Profile\ResetDefaultPassword;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('allows every active role to restore its own password to the default', function (Role $role) {
    $user = User::factory()->create([
        'role' => $role,
        'password' => Hash::make('senha-anterior'),
    ]);

    $this->actingAs($user)
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee('Redefinir senha para o padrão');

    Livewire::actingAs($user)
        ->test(ResetDefaultPassword::class)
        ->call('openConfirmation')
        ->assertSet('showConfirmation', true)
        ->assertSee('Deseja mesmo redefinir sua senha?')
        ->assertSee('password')
        ->assertSee('Você será desconectado imediatamente')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertSet('showConfirmation', false)
        ->assertRedirect(route('login'));

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user->getKey(),
        'action' => ActivityAction::USER_PASSWORD_RESET_TO_DEFAULT->value,
        'description' => 'O usuário redefiniu a própria senha para o padrão.',
    ]);
    expect(ActivityLog::query()->latest('id')->value('description'))->not->toContain('password');
})->with(Role::cases());

it('refuses the default password action for an inactive account', function () {
    $user = User::factory()->inactive()->create([
        'password' => Hash::make('senha-anterior'),
    ]);

    expect(fn () => app(ResetOwnPasswordToDefault::class)->handle($user))
        ->toThrow(AuthorizationException::class);

    expect(Hash::check('senha-anterior', $user->fresh()->password))->toBeTrue();
    $this->assertDatabaseMissing('activity_logs', [
        'user_id' => $user->getKey(),
        'action' => ActivityAction::USER_PASSWORD_RESET_TO_DEFAULT->value,
    ]);
});

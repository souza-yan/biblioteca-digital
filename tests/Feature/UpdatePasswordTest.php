<?php

use App\Models\User;
use App\Rules\MinimumPasswordStrength;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('password can be updated', function () {
    $this->actingAs($user = User::factory()->create());

    Livewire::test('profile.update-password-form')
        ->set('state', [
            'current_password' => config('accounts.default_password'),
            'password' => 'New-password1',
            'password_confirmation' => 'New-password1',
        ])
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertDispatched('ts-ui:toast', function (string $event, array $parameters): bool {
            return $parameters['type'] === 'success'
                && $parameters['title'] === 'Senha atualizada!'
                && $parameters['description'] === 'Sua nova senha foi salva com sucesso.';
        });

    expect(Hash::check('New-password1', $user->fresh()->password))->toBeTrue();
});

test('current password must be correct', function () {
    $this->actingAs($user = User::factory()->create());

    Livewire::test('profile.update-password-form')
        ->set('state', [
            'current_password' => 'wrong-password',
            'password' => 'New-password1',
            'password_confirmation' => 'New-password1',
        ])
        ->call('updatePassword')
        ->assertHasErrors(['current_password'])
        ->assertDispatched('ts-ui:toast', fn (string $event, array $parameters): bool => $parameters['type'] === 'error');

    expect(Hash::check(config('accounts.default_password'), $user->fresh()->password))->toBeTrue();
});

test('new passwords must match', function () {
    $this->actingAs($user = User::factory()->create());

    Livewire::test('profile.update-password-form')
        ->set('state', [
            'current_password' => config('accounts.default_password'),
            'password' => 'New-password1',
            'password_confirmation' => 'wrong-password',
        ])
        ->call('updatePassword')
        ->assertHasErrors(['password'])
        ->assertDispatched('ts-ui:toast', fn (string $event, array $parameters): bool => $parameters['type'] === 'error');

    expect(Hash::check(config('accounts.default_password'), $user->fresh()->password))->toBeTrue();
});

test('weak passwords cannot replace the current password', function (string $password) {
    $this->actingAs($user = User::factory()->create());
    $originalHash = $user->password;

    Livewire::test('profile.update-password-form')
        ->set('state', [
            'current_password' => config('accounts.default_password'),
            'password' => $password,
            'password_confirmation' => $password,
        ])
        ->call('updatePassword')
        ->assertHasErrors('password')
        ->assertDontSee(MinimumPasswordStrength::MESSAGE)
        ->assertNotDispatched('saved')
        ->assertDispatched('ts-ui:toast', fn (string $event, array $parameters): bool => $parameters['type'] === 'error');

    expect($user->fresh()->password)->toBe($originalHash);
})->with(['abcdefgh', '12345678', 'Ab1!']);

test('medium and strong passwords can replace the current password', function (string $password) {
    $this->actingAs($user = User::factory()->create());

    Livewire::test('profile.update-password-form')
        ->set('state', [
            'current_password' => config('accounts.default_password'),
            'password' => $password,
            'password_confirmation' => $password,
        ])
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertDispatched('ts-ui:toast', fn (string $event, array $parameters): bool => $parameters['type'] === 'success');

    expect(Hash::check($password, $user->fresh()->password))->toBeTrue();
})->with(['Abcdefgh', 'abcdefgh1', 'Abcdefgh1!']);

test('the current password cannot be reused', function () {
    $password = 'Existing-password1!';
    $this->actingAs($user = User::factory()->create(['password' => Hash::make($password)]));
    $originalHash = $user->password;

    Livewire::test('profile.update-password-form')
        ->set('state', [
            'current_password' => $password,
            'password' => $password,
            'password_confirmation' => $password,
        ])
        ->call('updatePassword')
        ->assertHasErrors(['password' => 'A nova senha deve ser diferente da senha atual.'])
        ->assertNotDispatched('saved')
        ->assertDispatched('ts-ui:toast', fn (string $event, array $parameters): bool => $parameters['type'] === 'error');

    expect($user->fresh()->password)->toBe($originalHash);
});

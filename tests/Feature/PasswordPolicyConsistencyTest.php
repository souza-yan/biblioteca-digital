<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\SetFirstLoginPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

test('all password change flows reject weak passwords and the current password', function (string $flow, string $password) {
    $user = User::factory()->teacher()->requiresPasswordChange()->create(['password' => Hash::make('Current-password1!')]);
    $this->actingAs($user);
    $originalHash = $user->password;
    $input = ['current_password' => 'Current-password1!', 'password' => $password, 'password_confirmation' => $password];

    expect(fn () => match ($flow) {
        'profile' => app(UpdateUserPassword::class)->update($user, $input),
        'first-login' => app(SetFirstLoginPassword::class)->handle($user, $input),
        'recovery' => app(ResetUserPassword::class)->reset($user, $input),
    })->toThrow(ValidationException::class);

    expect($user->fresh()->password)->toBe($originalHash);
})->with(['profile', 'first-login', 'recovery'])->with(['abcdefgh', '12345678', 'Ab1!', 'Current-password1!']);

test('all password change flows accept a different medium or strong password', function (string $flow, string $password) {
    $user = User::factory()->teacher()->requiresPasswordChange()->create(['password' => Hash::make('Current-password1!')]);
    $this->actingAs($user);
    $input = ['current_password' => 'Current-password1!', 'password' => $password, 'password_confirmation' => $password];

    match ($flow) {
        'profile' => app(UpdateUserPassword::class)->update($user, $input),
        'first-login' => app(SetFirstLoginPassword::class)->handle($user, $input),
        'recovery' => app(ResetUserPassword::class)->reset($user, $input),
    };

    expect(Hash::check($password, $user->fresh()->password))->toBeTrue()
        ->and($user->fresh()->must_change_password)->toBeFalse();
})->with(['profile', 'first-login', 'recovery'])->with(['Abcdefgh', 'New-password1!']);

test('password recovery explains the available support path when mail is not configured', function () {
    config()->set('mail.default', 'log');

    $this->get(route('password.request'))->assertOk()
        ->assertSee('Solicite a redefinição da senha à administração do sistema.')
        ->assertDontSee('action="'.route('password.email').'"', false)
        ->assertSee('Voltar para entrar');
});

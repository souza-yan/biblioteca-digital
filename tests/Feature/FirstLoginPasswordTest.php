<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\User\CreateUser;
use App\Actions\User\ResetOwnPasswordToDefault;
use App\Actions\User\ResetUserPasswordToDefault;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('requires teachers and management to change their initial password before accessing the application', function (string $role) {
    $user = ($role === 'staff' ? User::factory()->staffInSchool() : User::factory()->teacher())
        ->requiresPasswordChange()->create();

    $this->post('/login', ['login' => $user->cpf, 'password' => config('accounts.default_password')])
        ->assertRedirect(route('password.first-login'));
    $this->assertAuthenticatedAs($user);
    $this->get(route('dashboard'))->assertRedirect(route('password.first-login'));
    $this->get(route('profile.show'))->assertRedirect(route('password.first-login'));
    $this->get(route('password.first-login'))->assertOk()->assertSee('Defina sua nova senha')
        ->assertDontSee('name="current_password"', false)
        ->assertDontSee('Antes de acessar a Biblioteca');
})->with(['teacher', 'staff']);

it('does not force an administrator to change the initial password', function () {
    $admin = User::factory()->admin()->requiresPasswordChange()->create();

    $this->post('/login', ['login' => $admin->email, 'password' => config('accounts.default_password')])
        ->assertRedirect(route('dashboard', absolute: false));
    $this->get(route('dashboard'))->assertOk();
    $this->get(route('password.first-login'))->assertRedirect(route('dashboard'));
});

it('unlocks the application only after a confirmed personal password replaces the initial password', function () {
    $teacher = User::factory()->teacher()->requiresPasswordChange()->create();

    $this->actingAs($teacher)->post(route('password.first-login.update'), [
        'password' => 'MinhaSenhaNova123!', 'password_confirmation' => 'MinhaSenhaNova123!',
    ])->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();

    expect($teacher->fresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('MinhaSenhaNova123!', $teacher->fresh()->password))->toBeTrue()
        ->and(Hash::check(config('accounts.default_password'), $teacher->fresh()->password))->toBeFalse();
    $this->get(route('dashboard'))->assertOk();
});

it('keeps the application locked when the new password is invalid', function (string $password, string $confirmation) {
    $teacher = User::factory()->teacher()->requiresPasswordChange()->create();

    $this->actingAs($teacher)->from(route('password.first-login'))->post(route('password.first-login.update'), [
        'password' => $password, 'password_confirmation' => $confirmation,
    ])->assertSessionHasErrorsIn('updatePassword', ['password']);

    expect($teacher->fresh()->must_change_password)->toBeTrue()
        ->and(Hash::check(config('accounts.default_password'), $teacher->fresh()->password))->toBeTrue();
})->with([
    ['Atividade1!', 'Atividade1!'],
    ['SenhaNova123!', 'diferente'],
    ['curta', 'curta'],
]);

it('does not allow this endpoint to change passwords after the first access has been completed', function () {
    $teacher = User::factory()->teacher()->create();
    $password = $teacher->password;

    $this->actingAs($teacher)->post(route('password.first-login.update'), [
        'password' => 'OutraSenha123!', 'password_confirmation' => 'OutraSenha123!',
    ])->assertForbidden();

    expect($teacher->fresh()->password)->toBe($password);
});

it('rejects reusing the existing password when the first access password was assigned by an administrator', function () {
    $teacher = User::factory()->teacher()->requiresPasswordChange()->create(['password' => Hash::make('SenhaTemporaria123!')]);

    $this->actingAs($teacher)->post(route('password.first-login.update'), [
        'password' => 'SenhaTemporaria123!', 'password_confirmation' => 'SenhaTemporaria123!',
    ])->assertSessionHasErrorsIn('updatePassword', ['password']);

    expect($teacher->fresh()->must_change_password)->toBeTrue();
});

it('blocks protected JSON updates while allowing logout during the first access', function () {
    $teacher = User::factory()->teacher()->requiresPasswordChange()->create();
    $originalName = $teacher->name;

    $this->actingAs($teacher)->putJson('/user/profile-information', ['name' => 'Outro nome', 'email' => $teacher->email])
        ->assertForbidden()->assertJsonPath('redirect', route('password.first-login'));

    expect($teacher->fresh()->name)->toBe($originalName);
    $this->post(route('logout'))->assertRedirect();
    $this->assertGuest();
});

it('requires authentication to change the first access password', function () {
    $this->get(route('password.first-login'))->assertRedirect(route('login'));
    $this->post(route('password.first-login.update'), [])->assertRedirect(route('login'));
});

it('marks new teacher accounts for mandatory password change', function () {
    $admin = User::factory()->admin()->create();

    $teacher = app(CreateUser::class)->handle($admin, [
        'name' => 'Professora Ana', 'email' => 'ana@example.com', 'cpf' => '52998224725', 'role' => Role::TEACHER,
        'must_change_password' => false,
    ]);

    expect($teacher->must_change_password)->toBeTrue();
});

it('requires another password change after restoring a non administrator password to the default', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create(['password' => Hash::make('SenhaPessoal123!')]);

    app(ResetUserPasswordToDefault::class)->handle($admin, $teacher);
    expect($teacher->fresh()->must_change_password)->toBeTrue();

    $teacher->forceFill(['must_change_password' => false])->save();
    app(ResetOwnPasswordToDefault::class)->handle($teacher);
    expect($teacher->fresh()->must_change_password)->toBeTrue();

    app(ResetOwnPasswordToDefault::class)->handle($admin);
    expect($admin->fresh()->must_change_password)->toBeFalse();
});

it('clears the first access requirement after a successful forgotten password reset', function () {
    $teacher = User::factory()->teacher()->requiresPasswordChange()->create();

    app(ResetUserPassword::class)->reset($teacher, ['password' => 'NovaSenha123!', 'password_confirmation' => 'NovaSenha123!']);

    expect($teacher->fresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('NovaSenha123!', $teacher->fresh()->password))->toBeTrue();
});

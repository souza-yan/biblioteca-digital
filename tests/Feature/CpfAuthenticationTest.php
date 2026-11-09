<?php

use App\Models\User;

beforeEach(function () {
    config()->set('accounts.cpf_login_enabled', true);
});

it('authenticates teachers and management with a CPF and password', function (string $role, string $login) {
    $user = ($role === 'staff' ? User::factory()->staffInSchool() : User::factory()->teacher())
        ->create(['cpf' => '01234567890']);

    $response = $this->post('/login', ['login' => $login, 'password' => config('accounts.default_password'), 'remember' => 'on']);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
})->with([
    ['teacher', '01234567890'], ['teacher', '012.345.678-90'],
    ['staff', '01234567890'], ['staff', '012.345.678-90'],
]);

it('keeps administrator login by email without requiring a CPF', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->post('/login', ['login' => $admin->email, 'password' => config('accounts.default_password')]);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($admin);
});

it('does not authenticate an administrator by CPF even if one was stored', function () {
    User::factory()->admin()->create(['cpf' => '52998224725']);

    $this->post('/login', ['login' => '52998224725', 'password' => config('accounts.default_password')])
        ->assertSessionHasErrors('login');

    $this->assertGuest();
});

it('rejects teacher and management email login after the transition', function (string $role) {
    $user = ($role === 'staff' ? User::factory()->staffInSchool() : User::factory()->teacher())->create();

    $this->post('/login', ['login' => $user->email, 'password' => config('accounts.default_password')])
        ->assertSessionHasErrors('login');

    $this->assertGuest();
})->with(['teacher', 'staff']);

it('preserves legacy email login during CPF regularization', function () {
    config()->set('accounts.cpf_login_enabled', false);
    $teacher = User::factory()->teacher()->withoutCpf()->create();

    $this->post('/login', ['email' => $teacher->email, 'password' => config('accounts.default_password')])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($teacher);
});

it('rejects inactive accounts incorrect passwords and invalid CPF input', function (string $login, bool $active, string $password) {
    User::factory()->teacher()->create(['cpf' => '52998224725', 'is_active' => $active]);

    $this->post('/login', ['login' => $login, 'password' => $password])->assertSessionHasErrors('login');

    $this->assertGuest();
})->with([
    ['52998224725', false, 'Atividade1!'],
    ['52998224725', true, 'senha-incorreta'],
    ['52998224724', true, 'Atividade1!'],
    ['abc52998224725', true, 'Atividade1!'],
]);

it('shares the login rate limit between masked and unmasked CPF input', function () {
    User::factory()->teacher()->create(['cpf' => '52998224725']);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post('/login', ['login' => $attempt % 2 === 0 ? '52998224725' : '529.982.247-25', 'password' => 'errada'])
            ->assertSessionHasErrors('login');
    }

    $this->post('/login', ['login' => '529.982.247-25', 'password' => 'errada'])->assertTooManyRequests();
    $this->assertGuest();
});

it('still confirms the authenticated user password with a virtual login field', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)->post('/user/confirm-password', ['password' => config('accounts.default_password')])
        ->assertRedirect()->assertSessionHas('auth.password_confirmed_at');
});

<?php

use App\Actions\User\ToggleUserActive;
use App\Models\School;
use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => config('accounts.default_password'),
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('usuário inativo não consegue logar', function () {
    $user = User::factory()->inactive()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => config('accounts.default_password'),
    ]);

    $this->assertGuest();
});

test('usuário inativo já logado é expulso', function () {
    $user = User::factory()->inactive()->create();

    $this->actingAs($user)->get('/')->assertRedirect('/login');
});

test('usuário inativo já logado é expulso também das rotas de perfil', function () {
    $user = User::factory()->inactive()->create();

    $this->actingAs($user)->get('/user/profile')->assertRedirect('/login');
});

test('professor desativado pela gestão da sua única escola perde o acesso', function () {
    $school = School::factory()->create();
    $staff = User::factory()->staffInSchool($school)->create();
    $teacher = User::factory()->teacher()->create(['must_change_password' => false]);
    $teacher->schools()->attach($school->getKey(), ['is_active' => true]);

    app(ToggleUserActive::class)->handle($staff, $teacher);

    expect($teacher->fresh()->canAccessSystem())->toBeFalse();

    $this->actingAs($teacher)->get('/painel/biblioteca')->assertRedirect('/login');
});

test('professor desativado em todas as escolas não consegue logar', function () {
    $school = School::factory()->create();
    $teacher = User::factory()->teacher()->create(['cpf' => '52998224725']);
    $teacher->schools()->attach($school->getKey(), ['is_active' => false]);

    $this->post('/login', [
        'email' => '529.982.247-25',
        'password' => config('accounts.default_password'),
    ]);

    $this->assertGuest();
});

test('professor desativado em uma escola mantém acesso pela outra', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $staff = User::factory()->staffInSchool($schoolA)->create();
    $teacher = User::factory()->teacher()->create(['must_change_password' => false]);
    $teacher->schools()->attach($schoolA->getKey(), ['is_active' => true]);
    $teacher->schools()->attach($schoolB->getKey(), ['is_active' => true]);

    app(ToggleUserActive::class)->handle($staff, $teacher);

    expect($teacher->fresh()->canAccessSystem())->toBeTrue();
    $this->actingAs($teacher)->get('/painel/biblioteca')->assertOk();
});

test('professor reativado pela gestão volta a acessar', function () {
    $school = School::factory()->create();
    $staff = User::factory()->staffInSchool($school)->create();
    $teacher = User::factory()->teacher()->create(['must_change_password' => false]);
    $teacher->schools()->attach($school->getKey(), ['is_active' => false]);

    app(ToggleUserActive::class)->handle($staff, $teacher);

    $this->actingAs($teacher)->get('/painel/biblioteca')->assertOk();
});

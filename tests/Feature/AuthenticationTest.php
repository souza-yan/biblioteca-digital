<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
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

    test('login screen can be rendered', function () {
        $response = $this->get('/login');

        $response->assertStatus(200);
    });

    test('users can authenticate using the login screen', function () {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
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

    // --- NOVOS TESTES DE USUÁRIO INATIVO ---

    test('usuário inativo não consegue logar', function () {
        $user = User::factory()->inactive()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    });

    test('usuário ativo loga normalmente', function () {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    });

    test('usuário inativo já logado é expulso', function () {
        $user = User::factory()->inactive()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
    });
});

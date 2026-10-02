<?php

test('registration screen is not available', function () {
    $this->get('/register')->assertNotFound();
});

test('public registration cannot create users', function () {
    $this->post('/register', [
        'name' => 'Intruso',
        'email' => 'intruso@escola.com',
        'password' => 'Senha-forte-123',
        'password_confirmation' => 'Senha-forte-123',
    ]);

    $this->assertDatabaseMissing('users', ['email' => 'intruso@escola.com']);
    $this->assertGuest();
});

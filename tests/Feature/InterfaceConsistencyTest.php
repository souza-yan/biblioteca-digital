<?php

use App\Models\User;

test('profile provides a keyboard content shortcut accessible dialog and toast announcements', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee('href="#main-content"', false)
        ->assertSee('id="main-content"', false)
        ->assertSee('aria-live="polite"', false)
        ->assertSee('aria-modal="true"', false)
        ->assertSee('aria-labelledby=', false);
});

test('management pages ask for confirmation before changing account access', function (string $route, string $question) {
    User::factory()->teacher()->create();
    User::factory()->staffInSchool()->create();

    $this->actingAs(User::factory()->admin()->create())->get(route($route))
        ->assertOk()
        ->assertSee($question)
        ->assertSee('Cancelar');
})->with([
    ['painel.users', 'Alterar situação do professor?'],
    ['painel.schools', 'Alterar acesso do gestor?'],
]);

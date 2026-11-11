<?php

use App\Models\User;
use Laravel\Jetstream\Http\Livewire\LogoutOtherBrowserSessionsForm;
use Livewire\Livewire;

test('session logout confirmation stays outside the skeleton and above its backdrop', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test(LogoutOtherBrowserSessionsForm::class)
        ->call('confirmLogout')
        ->assertSet('confirmingLogout', true)
        ->assertDispatched('confirming-logout-other-browser-sessions')
        ->assertSee('Digite sua senha para confirmar');

    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$component->html());
    $xpath = new DOMXPath($document);
    $dialog = $xpath->query('//*[@role="dialog"]')->item(0);

    expect($dialog)->not->toBeNull();
    expect($xpath->query('ancestor::*[@data-skeleton-gate]', $dialog)->length)->toBe(0);
    expect($dialog->getAttribute('class'))->toContain('relative', 'z-10');
    expect($dialog->hasAttribute('wire:ignore.self'))->toBeTrue();
    $backdrop = $xpath->query('preceding-sibling::div[1]', $dialog)->item(0);

    expect($backdrop)->not->toBeNull();
    expect($backdrop->getAttribute('class'))->toContain('backdrop-blur-sm')
        ->not->toContain('transform', 'transition-all');
    expect($backdrop->hasAttribute('x-transition:enter'))->toBeFalse();
    expect($component->html())->toContain('wire:key="modal-', 'preventScroll: true')
        ->not->toContain('setTimeout(() => $refs.password.focus(), 250)');

    $component->set('confirmingLogout', false)->assertSet('confirmingLogout', false);
});

test('other browser sessions can be logged out', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(LogoutOtherBrowserSessionsForm::class)
        ->set('password', config('accounts.default_password'))
        ->call('logoutOtherBrowserSessions')
        ->assertSuccessful();
});

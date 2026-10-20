<?php

use App\Livewire\Profile\UpdateProfileInformationForm;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('current profile information is available', function () {
    $this->actingAs($user = User::factory()->create());

    $component = Livewire::test(UpdateProfileInformationForm::class);

    expect($component->state['name'])->toEqual($user->name);
    expect($component->state['email'])->toEqual($user->email);
});

test('profile information can be updated', function () {
    $this->actingAs($user = User::factory()->create());

    Livewire::test('profile.update-profile-information-form')
        ->set('state', ['name' => 'Test Name', 'email' => 'test@example.com'])
        ->call('updateProfileInformation');

    expect($user->fresh())
        ->name->toEqual('Test Name')
        ->email->toEqual('test@example.com');
});

test('profile photo can be updated without redirecting away from the profile', function () {
    Storage::fake('public');
    $this->actingAs($user = User::factory()->create());

    Livewire::test(UpdateProfileInformationForm::class)
        ->set('photo', UploadedFile::fake()->image('avatar.png', 300, 300))
        ->call('updateProfileInformation')
        ->assertHasNoErrors()
        ->assertNoRedirect()
        ->assertSet('photo', null)
        ->assertDispatched('saved')
        ->assertDispatched('refresh-navigation-menu');

    expect($user->fresh()->profile_photo_path)->not->toBeNull();
});

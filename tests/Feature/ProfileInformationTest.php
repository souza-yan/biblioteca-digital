<?php

use App\Livewire\NavigationMenu;
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

test('does not show role labels in the profile summary', function (string $role) {
    $user = match ($role) {
        'admin' => User::factory()->admin()->create(),
        'staff' => User::factory()->staffInSchool()->create(),
        'teacher' => User::factory()->teacher()->create(),
    };

    Livewire::actingAs($user)
        ->test(UpdateProfileInformationForm::class)
        ->assertDontSee($user->role->label())
        ->assertSee($user->email);
})->with(['admin', 'staff', 'teacher']);

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

    $navigation = Livewire::test(NavigationMenu::class);

    Livewire::test(UpdateProfileInformationForm::class)
        ->set('photo', UploadedFile::fake()->image('avatar.png', 300, 300))
        ->call('updateProfileInformation')
        ->assertHasNoErrors()
        ->assertNoRedirect()
        ->assertSet('photo', null)
        ->assertDispatched('saved')
        ->assertDispatched('refresh-navigation-menu');

    expect($user->fresh()->profile_photo_path)->not->toBeNull();

    Storage::disk('public')->assertExists($user->fresh()->profile_photo_path);

    $navigation->dispatch('refresh-navigation-menu')
        ->assertViewIs('livewire.navigation-menu')
        ->assertSee($user->fresh()->profile_photo_url);
});

test('profile photo accepts the configured maximum and exposes it in the form', function () {
    Storage::fake('public');
    $this->actingAs($user = User::factory()->create());

    Livewire::test(UpdateProfileInformationForm::class)
        ->assertSee('Tamanho máximo de 5,00 MB')
        ->assertSee('accept=".jpg,.jpeg,.png"', false)
        ->set('photo', UploadedFile::fake()->image('avatar.png')->size(5120))
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->fresh()->profile_photo_path)->not->toBeNull();
});

test('profile photo rejects files above the configured maximum', function () {
    Storage::fake('public');
    config()->set('profile.photo.max_size_kilobytes', 1);
    $this->actingAs(User::factory()->create());

    Livewire::test(UpdateProfileInformationForm::class)
        ->set('photo', UploadedFile::fake()->image('avatar.png')->size(2))
        ->call('updateProfileInformation')
        ->assertHasErrors('photo');
});

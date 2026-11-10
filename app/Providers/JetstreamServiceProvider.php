<?php

namespace App\Providers;

use App\Livewire\NavigationMenu;
use App\Livewire\Profile\UpdatePasswordForm;
use App\Livewire\Profile\UpdateProfileInformationForm;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('navigation-menu', NavigationMenu::class);
        Livewire::component('profile.update-password-form', UpdatePasswordForm::class);
        Livewire::component('profile.update-profile-information-form', UpdateProfileInformationForm::class);
    }
}

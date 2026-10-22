<?php

namespace App\Providers;

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
        Livewire::component('profile.update-profile-information-form', UpdateProfileInformationForm::class);
    }
}

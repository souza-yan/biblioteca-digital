<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Enums\Role;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Responses\LoginResponse;
use App\Models\User;
use App\Rules\Cpf;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(function (Request $request): ?User {
            $identifier = LoginRequest::identifier($request);
            $query = User::query();

            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $query->where('email', $identifier);
                if (config('accounts.cpf_login_enabled')) {
                    $query->where('role', Role::ADMIN);
                }
            } elseif (Cpf::isValid($identifier)) {
                $query->where('cpf', $identifier)->whereIn('role', [Role::STAFF, Role::TEACHER]);
            } else {
                return null;
            }

            $user = $query->first();

            if ($user && Hash::check($request->password, $user->password) && $user->canAccessSystem()) {
                return $user;
            }

            return null;
        });

        Fortify::confirmPasswordsUsing(fn (User $user, ?string $password): bool => $password !== null
            && $user->is_active && Hash::check($password, $user->password));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = hash('sha256', LoginRequest::identifier($request).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

    }
}

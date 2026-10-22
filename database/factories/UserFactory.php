<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\School;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'current_team_id' => null,
            'role' => Role::TEACHER,   // novo: padrão é o menos privilegiado
            'is_active' => true,       // novo
        ];
    }

    // Estados novos: use assim -> User::factory()->admin()->create()
    public function admin(): static
    {
        return $this->state(fn () => ['role' => Role::ADMIN]);
    }

    public function staff(): static
    {
        return $this->state(fn () => ['role' => Role::STAFF]);
    }

    public function staffInSchool(?School $school = null): static
    {
        return $this->staff()->afterCreating(function (User $staff) use ($school): void {
            $activeSchool = $school ?? School::factory()->create();
            $staff->schools()->attach($activeSchool->getKey(), ['is_active' => true]);
        });
    }

    public function teacher(): static
    {
        return $this->state(fn () => ['role' => Role::TEACHER]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user should have a personal team.
     */
    public function withPersonalTeam(?callable $callback = null): static
    {
        if (! Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(fn (array $attributes, User $user) => [
                    'name' => $user->name.'\'s Team',
                    'user_id' => $user->id,
                    'personal_team' => true,
                ])
                ->when(is_callable($callback), $callback),
            'ownedTeams'
        );
    }
}

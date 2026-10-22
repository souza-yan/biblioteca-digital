<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === Role::STAFF;
    }

    public function isTeacher(): bool
    {
        return $this->role === Role::TEACHER;
    }

    public function assignableRoles(): array
    {
        return match ($this->role) {
            Role::ADMIN => Role::cases(),
            Role::STAFF => [Role::TEACHER],
            default => [],
        };
    }

    /**
     * @return HasMany<Download, $this>
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * @return BelongsToMany<Material, $this>
     */
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'favorites')
            ->withPivot('created_at');
    }

    /**
     * @return BelongsToMany<School, $this>
     */
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_user')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function belongsToSchool(School $school): bool
    {
        return $this->schools()
            ->whereKey($school->getKey())
            ->wherePivot('is_active', true)
            ->exists();
    }

    public function hasSchoolMembership(School $school): bool
    {
        return $this->schools()
            ->whereKey($school->getKey())
            ->exists();
    }

    public function hasExactlyOneActiveSchool(): bool
    {
        return $this->school() !== null;
    }

    public function isExclusiveTo(School $school): bool
    {
        return $this->schools()->count() === 1
            && $this->belongsToSchool($school);
    }

    /**
     * Returns the sole school with an active membership, or null for zero or multiple memberships.
     * This method never chooses a context from a user's first related school.
     */
    public function school(): ?School
    {
        $activeSchools = $this->schools()
            ->wherePivot('is_active', true)
            ->limit(2)
            ->get();

        return $activeSchools->count() === 1 ? $activeSchools->first() : null;
    }
}

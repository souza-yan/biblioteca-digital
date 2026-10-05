<?php

namespace App\Models;

use App\Enums\MaterialStatus;
use Database\Factories\MaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    /** @use HasFactory<MaterialFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => MaterialStatus::DRAFT->value,
    ];

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'type',
        'author',
        'status',
        'published_at',
        'created_by',
        'current_version_id',
        'archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MaterialStatus::class,
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<MaterialVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(MaterialVersion::class);
    }

    /**
     * @return BelongsTo<MaterialVersion, $this>
     */
    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(MaterialVersion::class, 'current_version_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withPivot('created_at');
    }

    /**
     * @return HasMany<Download, $this>
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }
}

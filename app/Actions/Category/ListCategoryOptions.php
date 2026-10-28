<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ListCategoryOptions
{
    private const CACHE_KEY = 'category-options.all';

    /**
     * @return Collection<int, Category>
     */
    public function all(): Collection
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): Collection => Category::query()->orderBy('name')->get(),
        );
    }

    /**
     * @return Collection<int, Category>
     */
    public function active(): Collection
    {
        return $this->all()
            ->filter(fn (Category $category): bool => $category->is_active)
            ->values();
    }

    public static function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

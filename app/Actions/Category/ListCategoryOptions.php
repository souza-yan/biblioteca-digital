<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ListCategoryOptions
{
    private const CACHE_KEY = 'category-options.v2.all';

    private const LEGACY_CACHE_KEY = 'category-options.all';

    /**
     * @return Collection<int, Category>
     */
    public function all(): Collection
    {
        $attributes = $this->rememberCategoryAttributes();

        if (! is_array($attributes) || ! collect($attributes)->every(
            fn (mixed $attribute): bool => is_array($attribute)
        )) {
            Cache::forget(self::CACHE_KEY);
            $attributes = $this->rememberCategoryAttributes();
        }

        return Category::hydrate($attributes);
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
        Cache::forget(self::LEGACY_CACHE_KEY);
    }

    private function rememberCategoryAttributes(): mixed
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => Category::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category): array => $category->getAttributes())
                ->all(),
        );
    }
}

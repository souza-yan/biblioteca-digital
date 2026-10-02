<?php

namespace App\Actions\Category;

use App\Models\Category;

class UpdateCategory
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Category $category, array $attributes): Category
    {
        $category->update($attributes);

        return $category;
    }
}

<?php

namespace App\Actions\Category;

use App\Models\Category;

class CreateCategory
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes): Category
    {
        return Category::create($attributes);
    }
}

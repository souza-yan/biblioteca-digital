<?php

namespace App\Actions\Category;

use App\Models\Category;

class ToggleCategoryActive
{
    public function handle(Category $category): Category
    {
        $category->update(['is_active' => ! $category->is_active]);

        return $category;
    }
}

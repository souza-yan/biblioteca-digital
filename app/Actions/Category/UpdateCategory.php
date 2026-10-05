<?php

namespace App\Actions\Category;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateCategory
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, Category $category, array $attributes): Category
    {
        return DB::transaction(function () use ($actor, $category, $attributes): Category {
            $category->update($attributes);

            $this->logActivity->handle($actor, ActivityAction::CATEGORY_UPDATED, 'Categoria atualizada.');

            return $category;
        });
    }
}

<?php

namespace App\Actions\Category;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateCategory
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, Category $category, array $attributes): Category
    {
        $updatedCategory = DB::transaction(function () use ($actor, $category, $attributes): Category {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            $lockedCategory = Category::query()->lockForUpdate()->findOrFail($category->getKey());
            Gate::forUser($lockedActor)->authorize('update', $lockedCategory);
            $lockedCategory->update($attributes);

            $this->logActivity->handle($lockedActor, ActivityAction::CATEGORY_UPDATED, 'Categoria atualizada.');

            return $lockedCategory;
        });

        ListCategoryOptions::clear();

        return $updatedCategory;
    }
}

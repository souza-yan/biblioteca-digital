<?php

namespace App\Actions\Category;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ToggleCategoryActive
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor, Category $category): Category
    {
        return DB::transaction(function () use ($actor, $category): Category {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            $lockedCategory = Category::query()->lockForUpdate()->findOrFail($category->getKey());
            Gate::forUser($lockedActor)->authorize('toggleActive', $lockedCategory);
            $lockedCategory->update(['is_active' => ! $lockedCategory->is_active]);

            $this->logActivity->handle($lockedActor, ActivityAction::CATEGORY_TOGGLED, 'Status da categoria alterado.');

            return $lockedCategory;
        });
    }
}

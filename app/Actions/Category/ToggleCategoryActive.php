<?php

namespace App\Actions\Category;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ToggleCategoryActive
{
    public function __construct(private LogActivity $logActivity) {}

    public function handle(User $actor, Category $category): Category
    {
        return DB::transaction(function () use ($actor, $category): Category {
            $category->update(['is_active' => ! $category->is_active]);

            $this->logActivity->handle($actor, ActivityAction::CATEGORY_TOGGLED, 'Status da categoria alterado.');

            return $category;
        });
    }
}

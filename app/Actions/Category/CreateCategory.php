<?php

namespace App\Actions\Category;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateCategory
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): Category
    {
        Gate::forUser($actor)->authorize('create', Category::class);

        $category = DB::transaction(function () use ($actor, $attributes): Category {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->getKey());
            Gate::forUser($lockedActor)->authorize('create', Category::class);

            $category = Category::create($attributes);

            $this->logActivity->handle($lockedActor, ActivityAction::CATEGORY_CREATED, 'Categoria criada.');

            return $category;
        });

        ListCategoryOptions::clear();

        return $category;
    }
}

<?php

namespace App\Actions\Category;

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCategory
{
    public function __construct(private LogActivity $logActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, array $attributes): Category
    {
        return DB::transaction(function () use ($actor, $attributes): Category {
            $category = Category::create($attributes);

            $this->logActivity->handle($actor, ActivityAction::CATEGORY_CREATED, 'Categoria criada.');

            return $category;
        });
    }
}

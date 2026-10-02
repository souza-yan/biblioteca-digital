<?php

namespace App\Http\Controllers;

use App\Actions\User\CreateUser;
use App\Actions\User\ToggleUserActive;
use App\Actions\User\UpdateUser;
use App\Enums\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            // Gestão só enxerga professores; Admin vê todos
            ->when($request->user()->isStaff(), fn ($q) => $q->where('role', Role::TEACHER))
            ->orderBy('name')
            ->paginate(15);

        return response()->json($users);
    }

    public function store(StoreUserRequest $request, CreateUser $createUser)
    {
        $user = $createUser->handle($request->validated());

        return response()->json($user, 201);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $updateUser)
    {
        Gate::authorize('manage', $user);

        $user = $updateUser->handle($user, $request->validated());

        return response()->json($user);
    }

    public function toggleActive(Request $request, User $user, ToggleUserActive $toggleUserActive)
    {
        Gate::authorize('manage', $user);

        $user = $toggleUserActive->handle($request->user(), $user);

        return response()->json($user);
    }
}

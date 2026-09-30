<?php

namespace App\Http\Controllers;

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

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        return response()->json($user, 201);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        Gate::authorize('manage', $user);

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']); // não sobrescreve a senha com vazio
        }

        $user->update($data);

        return response()->json($user);
    }

    public function toggleActive(Request $request, User $user)
    {
        Gate::authorize('manage', $user);

        abort_if($user->is($request->user()), 422, 'Você não pode desativar a si mesmo.');

        $user->update(['is_active' => ! $user->is_active]);

        return response()->json($user);
    }
}

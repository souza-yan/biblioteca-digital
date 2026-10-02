<?php

namespace App\Livewire\Users;

use App\Actions\User\CreateUser;
use App\Actions\User\ToggleUserActive;
use App\Actions\User\UpdateUser;
use App\Enums\Role;
use App\Livewire\Forms\UserForm;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public UserForm $form;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingUserId = null;

    public function mount(): void
    {
        $this->authorizeManager();
    }

    public function updatedSearch(): void
    {
        $this->authorizeManager();
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->authorizeManager();
        $this->resetForm();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->authorizeManager();
        $this->resetForm();
        $this->showForm = false;
    }

    public function editUser(int $userId): void
    {
        $this->authorizeManager();
        $user = $this->findManagedUser($userId);
        $this->resetValidation();

        $this->editingUserId = $user->getKey();
        $this->form->userId = $user->getKey();
        $this->form->name = $user->name;
        $this->form->email = $user->email;
        $this->form->password = '';
        $this->form->password_confirmation = '';
        $this->form->role = $user->role->value;
        $this->showForm = true;
    }

    public function save(CreateUser $createUser, UpdateUser $updateUser): void
    {
        $this->authorizeManager();
        $user = $this->editingUserId === null
            ? null
            : $this->findManagedUser($this->editingUserId);

        $this->form->userId = $user?->getKey();
        $attributes = $this->form->validate();

        if ($user === null) {
            $createUser->handle($attributes);
        } else {
            $updateUser->handle($user, $attributes);
        }

        $this->resetForm();
        $this->showForm = false;
        $this->resetPage();
    }

    public function toggleActive(int $userId, ToggleUserActive $toggleUserActive): void
    {
        $actor = $this->authorizeManager();
        $user = $this->findManagedUser($userId);

        if ($user->is($actor)) {
            throw ValidationException::withMessages([
                'user' => 'Você não pode desativar a si mesmo.',
            ]);
        }

        $toggleUserActive->handle($actor, $user);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $actor = $this->authorizeManager();

        $users = User::query()
            ->when($actor->isStaff(), fn (Builder $query): Builder => $query->where('role', Role::TEACHER))
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(15);

        foreach ($users as $user) {
            Gate::authorize('manage', $user);
        }

        $roleOptions = collect($actor->assignableRoles())
            ->map(fn (Role $role): array => [
                'label' => $role->label(),
                'value' => $role->value,
            ])
            ->all();

        return view('livewire.users.user-manager', [
            'users' => $users,
            'roleOptions' => $roleOptions,
            'actor' => $actor,
        ]);
    }

    private function findManagedUser(int $userId): User
    {
        $user = User::query()->findOrFail($userId);

        Gate::authorize('manage', $user);

        return $user;
    }

    private function authorizeManager(): User
    {
        $actor = Auth::user();

        abort_unless(
            $actor instanceof User && ($actor->isAdmin() || $actor->isStaff()),
            403,
        );

        return $actor;
    }

    private function resetForm(): void
    {
        $this->form->reset();
        $this->editingUserId = null;
        $this->resetValidation();
    }
}

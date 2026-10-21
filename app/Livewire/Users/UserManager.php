<?php

namespace App\Livewire\Users;

use App\Actions\User\CreateUser;
use App\Actions\User\ResetUserPasswordToDefault;
use App\Actions\User\ToggleUserActive;
use App\Actions\User\UpdateUser;
use App\Enums\Role;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Livewire\Forms\UserForm;
use App\Models\School;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

class UserManager extends Component
{
    use Interactions;
    use InteractsWithCurrentUser;
    use WithPagination;

    public UserForm $form;

    public string $search = '';

    #[Locked]
    public ?int $selectedUserId = null;

    public bool $showForm = false;

    public bool $showPasswordResetConfirmation = false;

    #[Locked]
    public ?int $editingUserId = null;

    #[Locked]
    public ?int $passwordResetUserId = null;

    public function mount(): void
    {
        $this->authorizeManager();
    }

    public function updatedSearch(): void
    {
        $this->authorizeManager();
        $this->selectedUserId = null;
        $this->resetPage();
    }

    public function addSchool(): void
    {
        $actor = $this->authorizeManager();
        abort_unless($actor->isAdmin() && $this->form->role === Role::TEACHER->value, 403);

        if (count($this->form->school_ids) < 2) {
            $this->form->school_ids = array_pad($this->form->school_ids, 2, null);
        }
    }

    public function removeSchool(int $index): void
    {
        $actor = $this->authorizeManager();
        abort_unless($actor->isAdmin() && $this->form->role === Role::TEACHER->value, 403);

        unset($this->form->school_ids[$index]);
        $this->form->school_ids = array_values($this->form->school_ids);
        $this->resetValidation();
    }

    public function selectUser(int $userId): void
    {
        $this->authorizeManager();
        $user = $this->findManagedUser($userId);

        $this->selectedUserId = $user->getKey();
        $this->search = $user->name;
        $this->resetPage();
    }

    public function clearSelectedUser(): void
    {
        $this->authorizeManager();
        $this->selectedUserId = null;
        $this->search = '';
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $actor = $this->authorizeManager();
        Gate::authorize('create', User::class);
        $this->resetForm();
        $this->form->role = Role::TEACHER->value;
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
        $actor = $this->authorizeManager();
        $user = $this->findManagedUser($userId);
        $this->resetValidation();

        $this->editingUserId = $user->getKey();
        $this->form->userId = $user->getKey();
        $this->form->name = $user->name;
        $this->form->email = $user->email;
        $this->form->password = '';
        $this->form->password_confirmation = '';
        $this->form->role = $user->role->value;
        $this->form->school_id = null;
        $this->form->school_ids = $actor->isAdmin()
            ? $user->schools()
                ->wherePivot('is_active', true)
                ->pluck('schools.id')
                ->all()
            : [];
        $this->showForm = true;
    }

    public function save(CreateUser $createUser, UpdateUser $updateUser): void
    {
        $actor = $this->authorizeManager();

        $user = $this->editingUserId === null
            ? null
            : $this->findManagedUser($this->editingUserId);

        if ($user === null) {
            Gate::authorize('create', User::class);
        }

        $this->form->userId = $user?->getKey();
        $attributes = $this->form->validate();
        $attributes['school_ids'] = array_values(array_filter(
            $attributes['school_ids'] ?? [],
            fn (mixed $schoolId): bool => $schoolId !== null && $schoolId !== ''
        ));

        if ($user === null) {
            $createUser->handle($actor, $attributes);

            $this->toast()
                ->success(
                    $actor->isStaff() ? 'Professor cadastrado!' : 'Usuário cadastrado!',
                    $actor->isStaff()
                        ? 'O professor foi cadastrado e vinculado à escola.'
                        : 'O usuário foi cadastrado com sucesso.'
                )
                ->send();
        } else {
            if ($attributes['role'] !== $user->role->value) {
                Gate::authorize('changeRole', $user);
            }

            $updateUser->handle($actor, $user, $attributes);

            $this->toast()
                ->success(
                    'Usuário atualizado!',
                    'As informações do usuário foram atualizadas com sucesso.'
                )
                ->send();
        }

        $this->resetForm();
        $this->showForm = false;
        $this->resetPage();
    }

    public function toggleActive(
        int $userId,
        ToggleUserActive $toggleUserActive
    ): void {
        $actor = $this->authorizeManager();
        $user = $this->findManagedUser($userId);

        if ($user->is($actor)) {
            throw ValidationException::withMessages([
                'user' => 'Você não pode desativar a si mesmo.',
            ]);
        }

        Gate::authorize('toggleActive', $user);

        $toggleUserActive->handle($actor, $user);

        $statusIsActive = $actor->isStaff()
            ? (bool) $user->schools()
                ->whereKey($actor->school()?->getKey())
                ->first()?->pivot?->is_active
            : (bool) $user->fresh()->is_active;
        $statusLabel = $actor->isStaff() ? 'Vínculo nesta escola' : 'Conta global';

        $this->toast()
            ->success(
                'Status atualizado!',
                $actor->isStaff()
                    ? $statusLabel.' foi '.($statusIsActive ? 'ativado' : 'desativado').' com sucesso.'
                    : $statusLabel.' foi '.($statusIsActive ? 'ativada' : 'desativada').' com sucesso.'
            )
            ->send();
    }

    public function openPasswordResetConfirmation(int $userId): void
    {
        $user = $this->findManagedUser($userId);
        Gate::authorize('resetPassword', $user);

        $this->passwordResetUserId = $user->getKey();
        $this->showPasswordResetConfirmation = true;
    }

    public function closePasswordResetConfirmation(): void
    {
        $this->authorizeManager();
        $this->passwordResetUserId = null;
        $this->showPasswordResetConfirmation = false;
    }

    public function resetPassword(ResetUserPasswordToDefault $resetUserPasswordToDefault): void
    {
        $actor = $this->authorizeManager();
        $userId = $this->passwordResetUserId;
        abort_if($userId === null, 404);
        $user = $this->findManagedUser($userId);
        Gate::authorize('resetPassword', $user);

        $resetUserPasswordToDefault->handle($actor, $user);
        $this->passwordResetUserId = null;
        $this->showPasswordResetConfirmation = false;

        $this->toast()
            ->success('Senha redefinida!', 'A senha voltou ao padrão “password” e as sessões foram encerradas.')
            ->send();
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $actor = $this->authorizeManager();
        $manageableUsers = $this->scopeToManageableUsers(User::query(), $actor);
        $searchTerm = trim($this->search);
        $suggestions = $searchTerm !== '' && $this->selectedUserId === null
            ? (clone $manageableUsers)
                ->where(function (Builder $query) use ($searchTerm): void {
                    $query->where('name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('email', 'like', '%'.$searchTerm.'%');
                })
                ->orderBy('name')
                ->limit(3)
                ->get(['id', 'name', 'email'])
            : collect();
        $selectedUser = $this->selectedUserId === null
            ? null
            : (clone $manageableUsers)->whereKey($this->selectedUserId)->first(['id', 'name']);

        $usersQuery = clone $manageableUsers;

        if ($this->selectedUserId !== null) {
            $usersQuery->whereKey($this->selectedUserId);
        } elseif ($searchTerm !== '') {
            $usersQuery->where(function (Builder $query) use ($searchTerm): void {
                $query->where('name', 'like', '%'.$searchTerm.'%')
                    ->orWhere('email', 'like', '%'.$searchTerm.'%');
            });
        }

        $users = $usersQuery->orderBy('name')->paginate(15);

        $editingUser = $this->editingUserId === null
            ? null
            : $users->firstWhere('id', $this->editingUserId);

        foreach ($users as $user) {
            Gate::authorize('manage', $user);
        }

        $schoolOptions = $actor->isAdmin()
            ? School::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (School $school): array => [
                    'label' => $school->name,
                    'value' => $school->getKey(),
                ])
                ->all()
            : [];

        $teacherSchoolOptions = [];

        foreach (range(0, max(1, count($this->form->school_ids)) - 1) as $index) {
            $otherSchoolIds = $this->form->school_ids;
            unset($otherSchoolIds[$index]);

            $teacherSchoolOptions[$index] = array_values(array_filter(
                $schoolOptions,
                fn (array $option): bool => ! in_array($option['value'], $otherSchoolIds)
            ));
        }

        return view('livewire.users.user-manager', [
            'users' => $users,
            'suggestions' => $suggestions,
            'selectedUser' => $selectedUser,
            'columns' => [
                ['label' => 'Nome', 'class' => 'text-lg'],
                ['label' => 'E-mail', 'class' => 'text-lg'],
                ['label' => 'Ações', 'align' => 'right', 'class' => 'text-lg'],
            ],
            'schoolOptions' => $schoolOptions,
            'teacherSchoolOptions' => $teacherSchoolOptions,
            'actor' => $actor,
            'editingUser' => $editingUser,
            'passwordResetUser' => $this->passwordResetUserId === null
                ? null
                : User::query()->find($this->passwordResetUserId),
        ]);
    }

    private function findManagedUser(int $userId): User
    {
        $actor = $this->currentUser();
        $user = $this->scopeToManageableUsers(User::query(), $actor)->findOrFail($userId);

        Gate::authorize('manage', $user);

        return $user;
    }

    private function authorizeManager(): User
    {
        $actor = $this->currentUser();

        Gate::authorize('create', User::class);

        return $actor;
    }

    private function scopeToManageableUsers(Builder $query, User $actor): Builder
    {
        $query->where('role', Role::TEACHER);

        if (! $actor->isStaff()) {
            return $query;
        }

        $school = $actor->school();

        abort_unless($school !== null, 403);

        return $query
            ->where('role', Role::TEACHER)
            ->whereHas('schools', fn (Builder|BelongsToMany $schools): Builder|BelongsToMany => $schools
                ->where('schools.id', $school->getKey()))
            ->with(['schools' => fn (Builder|BelongsToMany $schools): Builder|BelongsToMany => $schools
                ->where('schools.id', $school->getKey())]);
    }

    private function resetForm(): void
    {
        $this->form->reset();
        $this->editingUserId = null;
        $this->resetValidation();
    }
}

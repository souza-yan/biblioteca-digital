<?php

namespace App\Livewire\Users;

use App\Actions\User\CreateUser;
use App\Actions\User\SendUserPasswordResetLink;
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
use Illuminate\Support\Facades\Password;
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

    #[Locked]
    public ?int $editingUserId = null;

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
        $this->authorizeManager();
        Gate::authorize('create', User::class);
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
        $this->form->school_id = $user->isStaff()
            ? $user->school()?->getKey()
            : null;
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

        if ($user === null) {
            $createUser->handle($actor, $attributes);

            $this->toast()
                ->success(
                    'Usuário cadastrado!',
                    'O usuário foi cadastrado com sucesso.'
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

    public function sendPasswordResetLink(
        int $userId,
        SendUserPasswordResetLink $sendUserPasswordResetLink,
    ): void {
        $actor = $this->authorizeManager();
        $user = $this->findManagedUser($userId);

        Gate::authorize('resetPassword', $user);

        try {
            $status = $sendUserPasswordResetLink->handle($actor, $user);
        } catch (\Throwable $exception) {
            report($exception);

            $this->toast()
                ->error('Não foi possível iniciar a redefinição de senha.')
                ->send();

            return;
        }

        if ($status === Password::RESET_LINK_SENT) {
            $this->toast()
                ->success('Solicitação de redefinição de senha enviada.')
                ->send();

            return;
        }

        $this->toast()
            ->error('Não foi possível iniciar a redefinição de senha.')
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

        $roleOptions = collect($actor->assignableRoles())
            ->map(fn (Role $role): array => [
                'label' => $role->label(),
                'value' => $role->value,
            ])
            ->all();
        $schoolOptions = $actor->isAdmin()
            ? School::query()->active()->orderBy('name')->get(['id', 'name'])
                ->map(fn (School $school): array => [
                    'label' => $school->name,
                    'value' => $school->getKey(),
                ])
                ->all()
            : [];

        return view('livewire.users.user-manager', [
            'users' => $users,
            'suggestions' => $suggestions,
            'selectedUser' => $selectedUser,
            'columns' => $actor->isStaff()
                ? [
                    ['label' => 'Nome', 'class' => 'text-lg'],
                    ['label' => 'E-mail', 'class' => 'text-lg'],
                    ['label' => 'Cargo', 'class' => 'text-lg'],
                    ['label' => 'Conta global', 'class' => 'text-lg'],
                    ['label' => 'Vínculo nesta escola', 'class' => 'text-lg'],
                    ['label' => 'Ações', 'align' => 'right', 'class' => 'text-lg'],
                ]
                : [
                    ['label' => 'Nome', 'class' => 'text-lg'],
                    ['label' => 'E-mail', 'class' => 'text-lg'],
                    ['label' => 'Cargo', 'class' => 'text-lg'],
                    ['label' => 'Situação', 'class' => 'text-lg'],
                    ['label' => 'Ações', 'align' => 'right', 'class' => 'text-lg'],
                ],
            'roleOptions' => $roleOptions,
            'schoolOptions' => $schoolOptions,
            'actor' => $actor,
            'editingUser' => $editingUser,
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
        if (! $actor->isStaff()) {
            return $query;
        }

        $school = $actor->school();

        abort_unless($school !== null, 403);

        return $query
            ->where('role', Role::TEACHER)
            ->whereHas('schools', fn (Builder|BelongsToMany $schools): Builder|BelongsToMany => $schools
                ->where('schools.id', $school->getKey())
                ->where('schools.is_active', true))
            ->with(['schools' => fn (Builder|BelongsToMany $schools): Builder|BelongsToMany => $schools
                ->where('schools.id', $school->getKey())
                ->where('schools.is_active', true)]);
    }

    private function resetForm(): void
    {
        $this->form->reset();
        $this->editingUserId = null;
        $this->resetValidation();
    }
}

<?php

namespace App\Livewire\Schools;

use App\Actions\School\CreateSchool;
use App\Actions\School\UpdateSchool;
use App\Actions\User\CreateUser;
use App\Actions\User\ResetUserPasswordToDefault;
use App\Actions\User\ToggleUserActive;
use App\Actions\User\UpdateUser;
use App\Enums\Role;
use App\Enums\SchoolRegion;
use App\Enums\SchoolType;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Livewire\Forms\SchoolForm;
use App\Models\School;
use App\Models\User;
use App\Rules\Cpf;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

class SchoolManager extends Component
{
    use Interactions;
    use InteractsWithCurrentUser;
    use WithPagination;

    public SchoolForm $form;

    public string $search = '';

    public bool $showSchoolForm = false;

    public bool $showManagerForm = false;

    #[Locked]
    public ?int $editingSchoolId = null;

    #[Locked]
    public ?int $managerSchoolId = null;

    public string $managerName = '';

    public string $managerEmail = '';

    public string $managerCpf = '';

    #[Locked]
    public ?int $editingManagerId = null;

    public ?int $managerDestinationSchoolId = null;

    public bool $showManagerPasswordResetConfirmation = false;

    #[Locked]
    public ?int $passwordResetManagerId = null;

    #[Locked]
    public ?int $passwordResetManagerSchoolId = null;

    public function mount(): void
    {
        Gate::authorize('viewAny', School::class);
    }

    public function updatedSearch(): void
    {
        Gate::authorize('viewAny', School::class);
        $this->resetPage();
    }

    public function openCreate(): void
    {
        Gate::authorize('create', School::class);
        $this->resetSchoolForm();
        $this->showSchoolForm = true;
    }

    public function editSchool(int $schoolId): void
    {
        $school = $this->findAuthorizedSchool($schoolId);

        $this->form->reset();
        $this->form->schoolId = $school->getKey();
        $this->form->name = $school->name;
        $this->form->type = $school->type->value;
        $this->form->region = $school->region->value;
        $this->form->address = $school->address;
        $this->form->phone = $school->phone ?? '';
        $this->editingSchoolId = $school->getKey();
        $this->resetValidation();
        $this->showSchoolForm = true;
    }

    public function closeSchoolForm(): void
    {
        Gate::authorize('viewAny', School::class);
        $this->resetSchoolForm();
        $this->showSchoolForm = false;
    }

    public function saveSchool(CreateSchool $createSchool, UpdateSchool $updateSchool): void
    {
        $actor = $this->currentUser();
        $school = $this->editingSchoolId === null
            ? null
            : $this->findAuthorizedSchool($this->editingSchoolId);

        Gate::authorize($school === null ? 'create' : 'update', $school ?? School::class);

        $this->form->schoolId = $school?->getKey();
        $attributes = $this->form->validate();

        if ($school === null) {
            $createSchool->handle($actor, $attributes);
            $title = 'Escola cadastrada!';
            $message = 'A escola foi adicionada ao sistema.';
        } else {
            $updateSchool->handle($actor, $school, $attributes);
            $title = 'Escola atualizada!';
            $message = 'As informações da escola foram salvas.';
        }

        $this->resetSchoolForm();
        $this->showSchoolForm = false;
        $this->resetPage();

        $this->toast()->success($title, $message)->send();
    }

    public function openManagerForm(int $schoolId): void
    {
        $school = $this->findAuthorizedSchool($schoolId);
        Gate::authorize('create', User::class);
        $this->resetManagerForm();
        $this->managerSchoolId = $school->getKey();
        $this->showManagerForm = true;
    }

    public function closeManagerForm(): void
    {
        Gate::authorize('viewAny', School::class);
        $this->resetManagerForm();
        $this->showManagerForm = false;
    }

    public function editManager(int $schoolId, int $managerId): void
    {
        $manager = $this->findAuthorizedManager($schoolId, $managerId);
        Gate::authorize('manage', $manager);
        Gate::authorize('updateCpf', $manager);

        $this->resetManagerForm();
        $this->editingManagerId = $manager->getKey();
        $this->managerSchoolId = $schoolId;
        $this->managerDestinationSchoolId = $schoolId;
        $this->managerName = $manager->name;
        $this->managerEmail = $manager->email;
        $this->managerCpf = $manager->cpf ?? '';
        $this->showManagerForm = true;
    }

    public function saveManager(CreateUser $createUser, UpdateUser $updateUser): void
    {
        $actor = $this->currentUser();
        Gate::authorize('create', User::class);

        $schoolId = $this->managerSchoolId;
        abort_if($schoolId === null, 404);
        $school = $this->findAuthorizedSchool($schoolId);
        $manager = $this->editingManagerId === null
            ? null
            : $this->findAuthorizedManager($schoolId, $this->editingManagerId);
        if ($manager !== null) {
            Gate::authorize('manage', $manager);
            Gate::authorize('updateCpf', $manager);
        }
        $this->managerCpf = (string) Cpf::normalize($this->managerCpf);
        try {
            $attributes = $this->validate([
                'managerName' => ['required', 'string', 'max:255'],
                'managerEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($manager?->getKey())],
                'managerCpf' => Cpf::rules($manager?->getKey()),
                'managerDestinationSchoolId' => $manager === null ? ['exclude'] : ['required', 'integer', Rule::exists('schools', 'id')],
            ], Cpf::messages('managerCpf'));

            $userAttributes = [
                'name' => $attributes['managerName'],
                'email' => $attributes['managerEmail'],
                'cpf' => $attributes['managerCpf'],
                'school_id' => $manager === null ? $school->getKey() : $attributes['managerDestinationSchoolId'],
            ];

            if ($manager === null) {
                $createUser->handle($actor, [...$userAttributes, 'role' => Role::STAFF->value]);
            } else {
                $updateUser->handle($actor, $manager, $userAttributes);
            }
        } catch (ValidationException $exception) {
            $this->toast()->error('Confira os dados do gestor', collect($exception->errors())->flatten()->first())->send();
            throw $exception;
        }

        $this->resetManagerForm();
        $this->showManagerForm = false;

        $this->toast()
            ->success(
                $manager === null ? 'Gestor cadastrado!' : 'Gestor atualizado!',
                $manager === null ? 'O gestor foi criado e vinculado à escola.' : 'As informações do gestor foram salvas.'
            )
            ->send();
    }

    public function openManagerPasswordResetConfirmation(int $schoolId, int $managerId): void
    {
        $manager = $this->findAuthorizedManager($schoolId, $managerId);
        Gate::authorize('resetPassword', $manager);
        $this->passwordResetManagerId = $manager->getKey();
        $this->passwordResetManagerSchoolId = $schoolId;
        $this->showManagerPasswordResetConfirmation = true;
    }

    public function closeManagerPasswordResetConfirmation(): void
    {
        Gate::authorize('viewAny', School::class);
        $this->passwordResetManagerId = null;
        $this->passwordResetManagerSchoolId = null;
        $this->showManagerPasswordResetConfirmation = false;
    }

    public function resetManagerPassword(ResetUserPasswordToDefault $resetUserPasswordToDefault): void
    {
        abort_if($this->passwordResetManagerSchoolId === null || $this->passwordResetManagerId === null, 404);
        $manager = $this->findAuthorizedManager($this->passwordResetManagerSchoolId, $this->passwordResetManagerId);
        Gate::authorize('resetPassword', $manager);
        $resetUserPasswordToDefault->handle($this->currentUser(), $manager);
        $this->closeManagerPasswordResetConfirmation();
        $this->toast()->success('Senha redefinida!', 'A senha voltou ao padrão “'.config('accounts.default_password').'”. As sessões foram encerradas e o gestor deverá trocar a senha no próximo acesso.')->send();
    }

    public function toggleManagerActive(
        int $schoolId,
        int $managerId,
        ToggleUserActive $toggleUserActive,
    ): void {
        $actor = $this->currentUser();
        $school = $this->findAuthorizedSchool($schoolId);
        $manager = $school->users()
            ->whereKey($managerId)
            ->where('role', Role::STAFF)
            ->wherePivot('is_active', true)
            ->firstOrFail();

        Gate::authorize('toggleActive', $manager);

        $manager = $toggleUserActive->handle($actor, $manager);

        $this->toast()
            ->success(
                'Status atualizado!',
                'O gestor foi '.($manager->is_active ? 'ativado' : 'desativado').' com sucesso.'
            )
            ->send();
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        Gate::authorize('viewAny', School::class);

        $schools = School::query()
            ->with(['users' => fn ($query) => $query
                ->where('role', Role::STAFF)
                ->wherePivot('is_active', true)
                ->orderBy('name')])
            ->when(filled($this->search), fn (Builder $query): Builder => $query
                ->where(function (Builder $query): void {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('address', 'like', '%'.$this->search.'%');
                }))
            ->orderBy('name')
            ->paginate(6);

        return view('livewire.schools.school-manager', [
            'schools' => $schools,
            'schoolTypes' => SchoolType::cases(),
            'schoolRegions' => SchoolRegion::cases(),
            'managerSchool' => $this->managerSchoolId === null
                ? null
                : School::query()->find($this->managerSchoolId),
            'managerSchoolOptions' => $this->showManagerForm && $this->editingManagerId !== null
                ? School::query()->orderBy('name')->get(['id', 'name']) : collect(),
            'passwordResetManager' => $this->passwordResetManagerId === null
                ? null : User::query()->find($this->passwordResetManagerId),
        ]);
    }

    private function findAuthorizedSchool(int $schoolId): School
    {
        $school = School::query()->findOrFail($schoolId);
        Gate::authorize('update', $school);

        return $school;
    }

    private function findAuthorizedManager(int $schoolId, int $managerId): User
    {
        return $this->findAuthorizedSchool($schoolId)->users()
            ->where('role', Role::STAFF)
            ->wherePivot('is_active', true)
            ->findOrFail($managerId);
    }

    private function resetSchoolForm(): void
    {
        $this->form->reset();
        $this->form->type = SchoolType::Emef->value;
        $this->editingSchoolId = null;
        $this->resetValidation();
    }

    private function resetManagerForm(): void
    {
        $this->managerName = '';
        $this->managerEmail = '';
        $this->managerCpf = '';
        $this->editingManagerId = null;
        $this->managerDestinationSchoolId = null;
        $this->managerSchoolId = null;
        $this->resetValidation();
    }
}

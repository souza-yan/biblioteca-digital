<?php

namespace App\Livewire\Schools;

use App\Actions\School\CreateSchool;
use App\Actions\School\ToggleSchoolActive;
use App\Actions\School\UpdateSchool;
use App\Actions\User\CreateUser;
use App\Enums\Role;
use App\Enums\SchoolRegion;
use App\Enums\SchoolType;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Livewire\Forms\SchoolForm;
use App\Models\School;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
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

    public function toggleActive(int $schoolId, ToggleSchoolActive $toggleSchoolActive): void
    {
        $actor = $this->currentUser();
        $school = $this->findAuthorizedSchool($schoolId);
        $school = $toggleSchoolActive->handle($actor, $school);

        $this->toast()
            ->success(
                'Status atualizado!',
                'A escola foi '.($school->is_active ? 'ativada' : 'desativada').' com sucesso.'
            )
            ->send();
    }

    public function openManagerForm(int $schoolId): void
    {
        $school = $this->findAuthorizedSchool($schoolId);
        abort_unless($school->is_active, 422);

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

    public function saveManager(CreateUser $createUser): void
    {
        $actor = $this->currentUser();
        Gate::authorize('create', User::class);

        $schoolId = $this->managerSchoolId;
        abort_if($schoolId === null, 404);
        $school = $this->findAuthorizedSchool($schoolId);
        abort_unless($school->is_active, 422);

        $attributes = $this->validate([
            'managerName' => ['required', 'string', 'max:255'],
            'managerEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
        ]);

        $createUser->handle($actor, [
            'name' => $attributes['managerName'],
            'email' => $attributes['managerEmail'],
            'role' => Role::STAFF->value,
            'school_id' => $school->getKey(),
        ]);

        $this->resetManagerForm();
        $this->showManagerForm = false;

        $this->toast()
            ->success('Gestor cadastrado!', 'O gestor foi criado e vinculado à escola.')
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
            ->paginate(12);

        return view('livewire.schools.school-manager', [
            'schools' => $schools,
            'schoolTypes' => SchoolType::cases(),
            'schoolRegions' => SchoolRegion::cases(),
            'managerSchool' => $this->managerSchoolId === null
                ? null
                : School::query()->find($this->managerSchoolId),
        ]);
    }

    private function findAuthorizedSchool(int $schoolId): School
    {
        $school = School::query()->findOrFail($schoolId);
        Gate::authorize('update', $school);

        return $school;
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
        $this->managerSchoolId = null;
        $this->resetValidation();
    }
}

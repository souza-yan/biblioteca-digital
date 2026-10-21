<?php

namespace App\Livewire\Forms;

use App\Enums\Role;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use App\Models\School;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;
use Livewire\Form;

class UserForm extends Form
{
    use InteractsWithCurrentUser;

    #[Locked]
    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = '';

    public ?int $school_id = null;

    /** @var array<int, int|string|null> */
    public array $school_ids = [];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $actor = $this->currentUser();
        $staffEditingExistingUser = $actor->isStaff() && $this->userId !== null;

        $schoolRules = ['prohibited'];
        $schoolIdsRules = $actor->isStaff() || $this->role !== Role::TEACHER->value
            ? ['prohibited']
            : ['array'];
        $passwordRules = $this->userId === null || $staffEditingExistingUser
            ? ['exclude']
            : ['nullable', 'confirmed', Password::defaults()];

        return [
            'name' => $staffEditingExistingUser ? ['exclude'] : ['required', 'string', 'max:255'],
            'email' => $staffEditingExistingUser ? ['exclude'] : [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => $passwordRules,
            'password_confirmation' => [$this->userId === null ? 'exclude' : 'nullable'],
            'role' => [
                $this->userId === null ? 'required' : 'sometimes',
                Rule::enum(Role::class)->only([Role::TEACHER]),
            ],
            'school_id' => $schoolRules,
            'school_ids' => $schoolIdsRules,
            'school_ids.*' => [
                'nullable',
                'integer',
                'distinct',
                Rule::exists((new School)->getTable(), 'id'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->currentUser()->isStaff()
            ? ['email.unique' => 'Não foi possível salvar o usuário com os dados informados.']
            : [];
    }
}

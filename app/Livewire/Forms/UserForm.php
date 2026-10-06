<?php

namespace App\Livewire\Forms;

use App\Enums\Role;
use App\Livewire\Concerns\InteractsWithCurrentUser;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Form;

class UserForm extends Form
{
    use InteractsWithCurrentUser;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $actor = $this->currentUser();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => [
                $this->userId === null ? 'required' : 'nullable',
                'confirmed',
                Password::defaults(),
            ],
            'role' => [
                $this->userId === null ? 'required' : 'sometimes',
                Rule::enum(Role::class)->only($actor->assignableRoles()),
            ],
        ];
    }
}

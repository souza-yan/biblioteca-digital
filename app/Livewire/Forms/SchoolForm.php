<?php

namespace App\Livewire\Forms;

use App\Enums\SchoolRegion;
use App\Enums\SchoolType;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class SchoolForm extends Form
{
    #[Locked]
    public ?int $schoolId = null;

    public string $name = '';

    public string $type = SchoolType::Emef->value;

    public string $region = '';

    public string $address = '';

    public string $phone = '';

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('schools', 'name')->ignore($this->schoolId)],
            'type' => ['required', Rule::enum(SchoolType::class)],
            'region' => ['required', Rule::enum(SchoolRegion::class)],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}

<?php

namespace App\Livewire\Forms;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class CategoryForm extends Form
{
    #[Locked]
    public ?int $categoryId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $is_active = true;

    public function generateSlugFromName(): void
    {
        if (trim($this->slug) === '') {
            $this->slug = Str::slug($this->name);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($this->categoryId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome da categoria é obrigatório.',
            'name.string' => 'O nome da categoria deve ser um texto.',
            'name.max' => 'O nome da categoria não pode ultrapassar 255 caracteres.',
            'slug.required' => 'O slug da categoria é obrigatório.',
            'slug.string' => 'O slug da categoria deve ser um texto.',
            'slug.max' => 'O slug da categoria não pode ultrapassar 255 caracteres.',
            'slug.unique' => 'O slug informado já está em uso.',
            'description.string' => 'A descrição deve ser um texto.',
            'is_active.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ];
    }
}

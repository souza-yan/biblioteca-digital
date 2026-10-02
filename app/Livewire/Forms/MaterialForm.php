<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;

class MaterialForm extends Form
{
    public ?int $materialId = null;

    public string $title = '';

    public string $description = '';

    public string $category_id = '';

    public string $type = '';

    public string $author = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $required = $this->materialId === null ? ['required'] : ['sometimes', 'required'];

        return [
            'title' => [...$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => [
                ...$required,
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'type' => [...$required, 'string', 'max:255'],
            'author' => [...$required, 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título do material é obrigatório.',
            'title.string' => 'O título do material deve ser um texto.',
            'title.max' => 'O título do material não pode ultrapassar 255 caracteres.',
            'description.string' => 'A descrição deve ser um texto.',
            'category_id.required' => 'A categoria é obrigatória.',
            'category_id.integer' => 'A categoria selecionada é inválida.',
            'category_id.exists' => 'A categoria selecionada não existe ou está inativa.',
            'type.required' => 'O tipo do material é obrigatório.',
            'type.string' => 'O tipo do material deve ser um texto.',
            'type.max' => 'O tipo do material não pode ultrapassar 255 caracteres.',
            'author.required' => 'O autor do material é obrigatório.',
            'author.string' => 'O autor do material deve ser um texto.',
            'author.max' => 'O autor do material não pode ultrapassar 255 caracteres.',
        ];
    }
}

<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class MaterialForm extends Form
{
    #[Locked]
    public ?int $materialId = null;

    public string $title = '';

    public string $description = '';

    public string $category_id = '';

    public string $type = '';

    public string $author = '';

    public mixed $file = null;

    public string $change_note = '';

    public string $initialStatus = 'draft';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $creating = $this->materialId === null;
        $required = $creating ? ['required'] : ['sometimes', 'required'];

        $rules = [
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

        if ($creating) {
            $rules['file'] = [
                'required',
                'file',
                'mimes:'.implode(',', config('materials.upload.allowed_mimes')),
                'max:'.config('materials.upload.max_size_kilobytes'),
            ];
            $rules['change_note'] = ['nullable', 'string'];
            $rules['initialStatus'] = ['required', 'string', Rule::in(['draft', 'published'])];
        }

        return $rules;
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
            'file.required' => 'O arquivo do material é obrigatório.',
            'file.file' => 'O arquivo enviado é inválido.',
            'file.mimes' => 'O formato do arquivo não é permitido.',
            'file.max' => 'O arquivo excede o tamanho máximo permitido.',
            'change_note.string' => 'A nota da versão deve ser um texto.',
            'initialStatus.required' => 'Selecione o status inicial do material.',
            'initialStatus.in' => 'O status inicial selecionado é inválido.',
        ];
    }
}

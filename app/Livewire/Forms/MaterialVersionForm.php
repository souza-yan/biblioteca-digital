<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class MaterialVersionForm extends Form
{
    public mixed $file = null;

    public string $change_note = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:'.implode(',', config('materials.upload.allowed_mimes')),
                'max:'.config('materials.upload.max_size_kilobytes'),
            ],
            'change_note' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'O arquivo da versão é obrigatório.',
            'file.file' => 'O arquivo enviado é inválido.',
            'file.mimes' => 'O formato do arquivo não é permitido.',
            'file.max' => 'O arquivo excede o tamanho máximo permitido.',
            'change_note.string' => 'A observação deve ser um texto.',
        ];
    }
}

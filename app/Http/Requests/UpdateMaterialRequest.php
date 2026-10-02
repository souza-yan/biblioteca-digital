<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'type' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
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

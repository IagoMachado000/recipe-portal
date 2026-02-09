<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('title')) {
            $this->merge([
                'title' => mb_strtolower(trim($this->title)),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:120',
                Rule::unique('recipes', 'title')->ignore($this->recipe),
            ],
            'description' => 'nullable|string|max:500',
            'ingredients' => 'required|array|min:1|max:20',
            'ingredients.*' => 'required|string|max:255',
            'steps' => 'required|string|min:10|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório',
            'title.min' => 'O título deve ter pelo menos :min caracteres',
            'title.max' => 'O título não pode ter mais que :max caracteres',
            'title.unique' => 'Este título já está em uso',

            'description.max' => 'A descrição não pode ter mais que :max caracteres',

            'ingredients.required' => 'Adicione pelo menos :min ingrediente',
            'ingredients.min' => 'Adicione pelo menos :min ingrediente',
            'ingredients.max' => 'Adicione no máximo :max ingredientes',
            'ingredients.*.required' => 'Cada ingrediente é obrigatório',
            'ingredients.*.max' => 'Cada ingrediente não pode ter mais que :max caracteres',

            'steps.required' => 'O modo de preparo é obrigatório',
            'steps.min' => 'O modo de preparo deve ter pelo menos :min caracteres',
            'steps.max' => 'O modo de preparo não pode ter mais que :max caracteres',
        ];
    }
}

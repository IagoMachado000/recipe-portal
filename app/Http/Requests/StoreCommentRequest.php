<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recipe_id' => 'required|exists:recipes,id',
            'body' => 'required|string|min:1|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'recipe_id.required' => 'A receita é obrigatória',
            'recipe_id.exists' => 'Receita não encontrada',
            'body.required' => 'O comentário é obrigatório',
            'body.min' => 'O comentário deve ter pelo menos :min caractere',
            'body.max' => 'O comentário não pode ter mais que :max caracteres',
        ];
    }
}

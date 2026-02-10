<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
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
            'score' => 'required|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'recipe_id.required' => 'A receita é obrigatória',
            'recipe_id.exists' => 'Receita não encontrada',
            'score.required' => 'A avaliação é obrigatória',
            'score.integer' => 'A avaliação deve ser um número inteiro',
            'score.min' => 'A avaliação mínima é :min estrela',
            'score.max' => 'A avaliação máxima é :max estrelas',
        ];
    }
}

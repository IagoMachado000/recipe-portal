<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecipeSearchRequest extends FormRequest
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
            'search' => 'nullable|string|max:100',
            'filter' => 'nullable|in:date,title,rating',
            'sort_order' => 'nullable|in:asc,desc',
            'sort_by' => 'nullable|in:created_at,title,rating_avg',
        ];
    }

    public function messages(): array
    {
        return [
            'search.max' => 'A busca pode ter no máximo :max caracteres',
            'filter.in' => 'Filtro inválido',
            'sort_order.in' => 'Ordem inválida',
            'sort_by.in' => 'Ordenação inválida',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('search')) {
            $this->merge([
                'search' => trim($this->search),
            ]);
        }

        if ($this->has('filter')) {
            switch ($this->filter) {
                case 'date':
                    $this->merge([
                        'sort_by' => 'created_at',
                        'sort_order' => $this->get('sort_order', 'desc'),
                    ]);
                    break;
                case 'title':
                    $this->merge([
                        'sort_by' => 'title',
                        'sort_order' => $this->get('sort_order', 'asc'),
                    ]);
                    break;
                case 'rating':
                    $this->merge([
                        'sort_by' => 'rating_avg',
                        'sort_order' => $this->get('sort_order', 'desc'),
                    ]);
                    break;
            }
        }
    }
}

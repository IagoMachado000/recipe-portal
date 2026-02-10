<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\RecipeDTO;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RecipeService
{
    public function getPublishedRecipes(array $filters = [], int $perPage = 9): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Recipe::withCount(['ratings', 'comments'])
            ->where('title', 'ILIKE', '%' . ($filters['search'] ?? '') . '%');

        // Aplicar filtro por tipo (se selecionado)
        $this->applySimpleFilter($query, $filters);

        // Aplicar ordenação
        $this->applySimpleSorting($query, $filters);

        return $query->paginate($perPage);
    }

    public function getUserRecipes(User $user, array $filters = [], int $perPage = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $user->recipes()
            ->withCount(['ratings', 'comments'])
            ->where('title', 'ILIKE', '%' . ($filters['search'] ?? '') . '%');

        $this->applySimpleFilter($query, $filters);

        $this->applySimpleSorting($query, $filters);

        return $query->paginate($perPage);
    }

    public function create(RecipeDTO $dto): Recipe
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();

            $data['user_id'] = Auth::id();

            if (!$data['user_id']) {
                throw new \RuntimeException('Usuário não autenticado.');
            }

            $data['slug'] = Str::slug($data['title']);

            $recipe = Recipe::create($data);

            Log::info('Receita criada com sucesso', ['recipe_id' => $recipe->id]);

            return $recipe;
        });
    }

    public function update(Recipe $recipe, RecipeDTO $dto): Recipe
    {
        return DB::transaction(function () use ($recipe, $dto) {
            $data = $dto->toArray();

            unset($data['user_id']); // Não alterar autor

            $data['slug'] = Str::slug($data['title']);

            $recipe->update($data);

            Log::info('Receita atualizada com sucesso', ['recipe_id' => $recipe->id]);

            return $recipe->fresh();
        });
    }

    public function delete(Recipe $recipe): bool
    {
        return DB::transaction(function () use ($recipe) {
            $recipe->delete();

            Log::info('Receita deletada com sucesso', ['recipe_id' => $recipe->id]);

            return true;
        });
    }

    private function applySimpleFilter($query, array $filters): void
    {
        if (!isset($filters['filter']) || empty($filters['filter'])) {
            return; // Retorna se não houver filtro
        }

        switch ($filters['filter']) {
            case 'date':
                $query->whereNotNull('created_at'); // Apenas com data válida
                break;
            case 'rating':
                $query->whereNotNull('rating_avg'); // Apenas com avaliações
                break;
            case 'title':
                // Já está aplicado na busca base
                break;
        }
    }

    private function applySimpleSorting($query, array $filters): void
    {
        $sortBy = 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        if (isset($filters['sort_by'])) {
            $sortBy = $filters['sort_by'];
        } else if (isset($filters['filter'])) {
            switch ($filters['filter']) {
                case 'date':
                    $sortBy = 'created_at';
                    break;
                case 'title':
                    $sortBy = 'title';
                    break;
                case 'rating':
                    $sortBy = 'rating_avg';
                    break;
            }
        }

        $query->orderBy($sortBy, $sortOrder);
    }
}

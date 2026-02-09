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
    public function getPublishedRecipes(int $perPage = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Recipe::withCount(['ratings', 'comments'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getUserRecipes(User $user, int $perPage = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $user->recipes()
            ->withCount(['ratings', 'comments'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
}

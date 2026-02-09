<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecipePolicy
{
    use HandlesAuthorization;

    /**
     * Apenas o autor da receita pode atualizar.
     */
    public function update(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    /**
     * Apenas o autor da receita pode excluir.
     */
    public function delete(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    /**
     * Apenas o autor da receita pode restaurar (soft delete).
     */
    public function restore(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    /**
     * Apenas o autor da receita pode excluir permanentemente.
     */
    public function forceDelete(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }
}

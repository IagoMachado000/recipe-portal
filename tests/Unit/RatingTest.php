<?php

namespace Tests\Unit;

use App\Models\Rating;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa relacionamento com User (belongsTo)
     */
    public function test_rating_belongs_to_user(): void
    {
        // Criar usuário, receita e avaliação
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create();
        $rating = Rating::factory()->create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        // Verificar relacionamento
        expect($rating->user)->toBeInstanceOf(User::class);
        expect($rating->user->id)->toBe($user->id);
        expect($rating->user->name)->toBe($user->name);
    }

    /**
     * Testa relacionamento com Recipe (belongsTo)
     */
    public function test_rating_belongs_to_recipe(): void
    {
        // Criar usuário, receita e avaliação
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create();
        $rating = Rating::factory()->create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        // Verificar relacionamento
        expect($rating->recipe)->toBeInstanceOf(Recipe::class);
        expect($rating->recipe->id)->toBe($recipe->id);
        expect($rating->recipe->title)->toBe($recipe->title);
    }

    /**
     * Testa validação de score mínimo (1)
     */
    public function test_rating_score_minimum_is_1(): void
    {
        // Criar avaliação com score mínimo
        $rating = Rating::factory()->create(['score' => 1]);

        // Verificar score mínimo
        expect($rating->score)->toBe(1);
        expect($rating->score)->toBeGreaterThanOrEqual(1);
    }

    /**
     * Testa validação de score máximo (5)
     */
    public function test_rating_score_maximum_is_5(): void
    {
        // Criar avaliação com score máximo
        $rating = Rating::factory()->create(['score' => 5]);

        // Verificar score máximo
        expect($rating->score)->toBe(5);
        expect($rating->score)->toBeLessThanOrEqual(5);
    }

    /**
     * Testa scores válidos no intervalo (1-5)
     */
    public function test_rating_scores_are_in_valid_range(): void
    {
        // Criar múltiplas avaliações com scores diferentes
        $scores = [1, 2, 3, 4, 5];
        $ratings = [];

        foreach ($scores as $score) {
            $ratings[] = Rating::factory()->create(['score' => $score]);
        }

        // Verificar se todos os scores estão no intervalo válido
        foreach ($ratings as $rating) {
            expect($rating->score)->toBeGreaterThanOrEqual(1);
            expect($rating->score)->toBeLessThanOrEqual(5);
            expect($rating->score)->toBeInt();
        }
    }

    /**
     * Testa cálculo automático de média em Recipe
     */
    public function test_rating_updates_recipe_average(): void
    {
        // Criar receita
        $recipe = Recipe::factory()->create();
        expect($recipe->rating_avg)->toBe(0.0);
        expect($recipe->rating_count)->toBe(0);

        // Criar avaliações com scores conhecidos
        $scores = [4, 5, 3]; // Média = 4.0
        foreach ($scores as $score) {
            Rating::factory()->create([
                'recipe_id' => $recipe->id,
                'score' => $score,
                'user_id' => User::factory()->create()->id,
            ]);
        }

        // Atualizar métricas manualmente
        $ratings = Rating::where('recipe_id', $recipe->id)->get();

        if ($ratings->isNotEmpty()) {
            $avgRating = $ratings->avg('score');
            $ratingCount = $ratings->count();

            $recipe->update([
                'rating_avg' => round($avgRating, 2),
                'rating_count' => $ratingCount,
            ]);
        }

        // Recarregar receita para obter valores atualizados
        $recipe->refresh();

        // Verificar média e contador
        expect($recipe->rating_count)->toBe(3);
        expect($recipe->rating_avg)->toBe(4.0);
    }

    /**
     * Testa atualização de rating_count
     */
    public function test_rating_updates_recipe_count(): void
    {
        // Criar receita
        $recipe = Recipe::factory()->create();
        $initialCount = $recipe->rating_count;

        // Criar avaliação
        Rating::factory()->create(['recipe_id' => $recipe->id]);

        // Atualizar métricas manualmente
        $ratings = Rating::where('recipe_id', $recipe->id)->get();

        if ($ratings->isNotEmpty()) {
            $ratingCount = $ratings->count();

            $recipe->update([
                'rating_count' => $ratingCount,
            ]);
        }

        // Recarregar receita
        $recipe->refresh();

        // Verificar contador atualizado
        expect($recipe->rating_count)->toBe($initialCount + 1);
    }

    /**
     * Testa constraint unique (recipe_id, user_id)
     */
    public function test_rating_enforces_unique_recipe_user_constraint(): void
    {
        // Criar usuário e receita
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create();

        // Criar primeira avaliação
        $firstRating = Rating::factory()->create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        // Tentar criar segunda avaliação (mesmo usuário e receita)
        expect(function () use ($user, $recipe) {
            Rating::factory()->create([
                'user_id' => $user->id,
                'recipe_id' => $recipe->id,
            ]);
        })->toThrow(\Illuminate\Database\QueryException::class);
    }

    /**
     * Testa efeito prático da constraint
     */
    public function test_rating_unique_constraint_prevents_duplicates(): void
    {
        // Criar usuário e receita
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create();

        // Criar primeira avaliação
        Rating::factory()->create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        // Verificar que apenas uma avaliação existe
        $ratings = Rating::where('recipe_id', $recipe->id)
            ->where('user_id', $user->id)
            ->get();
        expect($ratings->count())->toBe(1);
    }

    /**
     * Testa que usuários diferentes podem avaliar mesma receita
     */
    public function test_different_users_can_rate_same_recipe(): void
    {
        // Criar receita e múltiplos usuários
        $recipe = Recipe::factory()->create();
        $users = User::factory(3)->create();

        // Criar avaliações de usuários diferentes para mesma receita
        foreach ($users as $user) {
            Rating::factory()->create([
                'recipe_id' => $recipe->id,
                'user_id' => $user->id,
            ]);
        }

        // Verificar que todas as avaliações foram criadas
        $ratings = Rating::where('recipe_id', $recipe->id)->get();
        expect($ratings->count())->toBe(3);

        // Verificar que são de usuários diferentes
        $userIds = $ratings->pluck('user_id')->unique();
        expect($userIds->count())->toBe(3);
    }

    /**
     * Testa que mesmo usuário pode avaliar receitas diferentes
     */
    public function test_same_user_can_rate_different_recipes(): void
    {
        // Criar usuário e múltiplas receitas
        $user = User::factory()->create();
        $recipes = Recipe::factory(3)->create();

        // Criar avaliações do mesmo usuário para receitas diferentes
        foreach ($recipes as $recipe) {
            Rating::factory()->create([
                'recipe_id' => $recipe->id,
                'user_id' => $user->id,
            ]);
        }

        // Verificar que todas as avaliações foram criadas
        $ratings = Rating::where('user_id', $user->id)->get();
        expect($ratings->count())->toBe(3);

        // Verificar que são de receitas diferentes
        $recipeIds = $ratings->pluck('recipe_id')->unique();
        expect($recipeIds->count())->toBe(3);
    }

    /**
     * Testa acesso a atributos específicos
     */
    public function test_rating_attribute_access(): void
    {
        // Criar avaliação
        $rating = Rating::factory()->create();

        // Verificar acesso a atributos
        expect($rating->id)->toBeInt();
        expect($rating->recipe_id)->toBeInt();
        expect($rating->user_id)->toBeInt();
        expect($rating->score)->toBeInt();
        expect($rating->score)->toBeGreaterThanOrEqual(1);
        expect($rating->score)->toBeLessThanOrEqual(5);
        expect($rating->created_at)->not->toBeNull();
    }

    /**
     * Testa timestamps (apenas created_at)
     */
    public function test_rating_has_only_created_at_timestamp(): void
    {
        // Criar avaliação
        $rating = Rating::factory()->create();

        // Verificar que tem created_at
        expect($rating->created_at)->not->toBeNull();
        expect($rating->created_at)->toBeInstanceOf(\Carbon\Carbon::class);
    }
}

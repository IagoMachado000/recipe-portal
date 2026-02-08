<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Rating;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa relacionamento com User (belongsTo)
     */
    public function test_recipe_belongs_to_user(): void
    {
        // Criar usuário e receita
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        // Verificar relacionamento
        expect($recipe->user)->toBeInstanceOf(User::class);
        expect($recipe->user->id)->toBe($user->id);
        expect($recipe->user->name)->toBe($user->name);
    }

    /**
     * Testa relacionamento com Comments (hasMany)
     */
    public function test_recipe_has_many_comments(): void
    {
        // Criar receita com comentários
        $recipe = Recipe::factory()->create();
        $comments = Comment::factory(3)->create(['recipe_id' => $recipe->id]);

        // Verificar relacionamento
        expect($recipe->comments)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
        expect($recipe->comments()->count())->toBe(3);

        // Verificar se todos os comentários pertencem à receita
        foreach ($recipe->comments as $comment) {
            expect($comment->recipe_id)->toBe($recipe->id);
        }
    }

    /**
     * Testa relacionamento com Ratings (hasMany)
     */
    public function test_recipe_has_many_ratings(): void
    {
        // Criar receita com avaliações
        $recipe = Recipe::factory()->create();
        $ratings = Rating::factory(5)->create(['recipe_id' => $recipe->id]);

        // Verificar relacionamento
        expect($recipe->ratings)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
        expect($recipe->ratings()->count())->toBe(5);

        // Verificar se todas as avaliações pertencem à receita
        foreach ($recipe->ratings as $rating) {
            expect($rating->recipe_id)->toBe($recipe->id);
        }
    }

    /**
     * Testa cast de ingredients como array
     */
    public function test_recipe_casts_ingredients_to_array(): void
    {
        // Criar receita
        $recipe = Recipe::factory()->create();

        // Verificar se ingredients é array
        expect($recipe->ingredients)->toBeArray();
        expect($recipe->ingredients)->not->toBeEmpty();

        // Verificar se contém ingredientes válidos
        foreach ($recipe->ingredients as $ingredient) {
            expect($ingredient)->toBeString();
            expect($ingredient)->not->toBeEmpty();
        }
    }

    /**
     * Testa cast de steps como array
     */
    public function test_recipe_casts_steps_to_array(): void
    {
        // Criar receita
        $recipe = Recipe::factory()->create();

        // Verificar se steps é array
        expect($recipe->steps)->toBeArray();
        expect($recipe->steps)->not->toBeEmpty();

        // Verificar se contém passos válidos
        foreach ($recipe->steps as $step) {
            expect($step)->toBeString();
            expect($step)->not->toBeEmpty();
            expect($step)->toContain('Passo');
        }
    }

    /**
     * Testa atributos fillable
     */
    public function test_recipe_fillable_attributes(): void
    {
        // Criar receita com atributos específicos
        $recipe = new Recipe([
            'user_id' => 1,
            'title' => 'Título Teste',
            'description' => 'Descrição Teste',
            'ingredients' => ['ingrediente1', 'ingrediente2'],
            'steps' => ['Passo 1: teste'],
            'rating_avg' => 4.5,
            'rating_count' => 10,
        ]);

        // Verificar se atributos foram preenchidos
        expect($recipe->user_id)->toBe(1);
        expect($recipe->title)->toBe('Título Teste');
        expect($recipe->description)->toBe('Descrição Teste');
        expect($recipe->ingredients)->toBeArray();
        expect($recipe->steps)->toBeArray();
        expect($recipe->rating_avg)->toBe(4.5);
        expect($recipe->rating_count)->toBe(10);
    }

    /**
     * Testa soft deletes
     */
    public function test_recipe_uses_soft_deletes(): void
    {
        // Criar receita
        $recipe = Recipe::factory()->create();
        $originalId = $recipe->id;

        // Verificar que receita existe
        expect(Recipe::count())->toBe(1);
        expect(Recipe::find($originalId))->not->toBeNull();

        // Soft delete
        $recipe->delete();

        // Verificar que não aparece mais em consultas normais
        expect(Recipe::count())->toBe(0);
        expect(Recipe::find($originalId))->toBeNull();

        // Verificar que ainda existe com withTrashed
        expect(Recipe::withTrashed()->count())->toBe(1);
        expect(Recipe::withTrashed()->find($originalId))->not->toBeNull();

        // Verificar que tem deleted_at
        $trashedRecipe = Recipe::withTrashed()->find($originalId);
        expect($trashedRecipe->deleted_at)->not->toBeNull();
    }

    /**
     * Testa escopo de receitas não deletadas
     */
    public function test_recipe_scope_excludes_deleted(): void
    {
        // Criar múltiplas receitas
        $recipes = Recipe::factory(3)->create();

        // Deletar uma receita
        $recipes[0]->delete();

        // Verificar que apenas 2 aparecem em consultas normais
        expect(Recipe::count())->toBe(2);

        // Verificar que as 3 aparecem com withTrashed
        expect(Recipe::withTrashed()->count())->toBe(3);

        // Verificar que a deletada não aparece
        expect(Recipe::find($recipes[0]->id))->toBeNull();
        expect(Recipe::find($recipes[1]->id))->not->toBeNull();
        expect(Recipe::find($recipes[2]->id))->not->toBeNull();
    }

    /**
     * Testa restauração de soft delete
     */
    public function test_recipe_can_be_restored(): void
    {
        // Criar e deletar receita
        $recipe = Recipe::factory()->create();
        $recipe->delete();

        // Verificar que está deletada
        expect(Recipe::count())->toBe(0);
        expect(Recipe::withTrashed()->count())->toBe(1);

        // Restaurar
        $recipe->restore();

        // Verificar que foi restaurada
        expect(Recipe::count())->toBe(1);
        expect(Recipe::withTrashed()->count())->toBe(1);
        expect($recipe->deleted_at)->toBeNull();
    }

    /**
     * Testa acesso a atributos específicos
     */
    public function test_recipe_attribute_access(): void
    {
        // Criar receita
        $recipe = Recipe::factory()->create();

        // Verificar acesso a atributos
        expect($recipe->id)->toBeInt();
        expect($recipe->user_id)->toBeInt();
        expect($recipe->title)->toBeString();
        expect(strlen($recipe->title))->toBeLessThanOrEqual(120);
        expect($recipe->rating_avg)->toBeFloat();
        expect($recipe->rating_count)->toBeInt();
    }
}

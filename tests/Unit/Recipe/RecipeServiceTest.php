<?php

namespace Tests\Unit\Recipe;

use App\DTOs\RecipeDTO;
use App\Models\Recipe;
use App\Models\User;
use App\Services\RecipeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RecipeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_recipe_with_valid_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $data = [
            'title' => 'Bolo De Chocolate',
            'description' => 'Delicioso bolo de chocolate',
            'ingredients' => ['Farinha', 'Açúcar', 'Chocolate', 'Ovos'],
            'steps' => "Misture os ingredientes\nAsse por 30 minutos",
        ];

        $dto = RecipeDTO::fromArray($data);
        $service = new RecipeService();

        $recipe = $service->create($dto);

        expect($recipe)->toBeInstanceOf(Recipe::class);
        expect($recipe->title)->toBe('bolo de chocolate');
        expect($recipe->description)->toBe('Delicioso bolo de chocolate');
        expect($recipe->ingredients)->toBeArray();
        expect($recipe->ingredients)->toContain('Farinha');
        expect($recipe->steps)->toBeArray();
        expect($recipe->steps)->toContain('Passo 1: Misture os ingredientes');
        expect($recipe->user_id)->toBe($user->id);
        expect($recipe->slug)->toBe('bolo-de-chocolate');
        expect($recipe->rating_avg)->toBe(0.0);
        expect($recipe->rating_count)->toBe(0);
    }

    public function test_update_recipe_with_valid_data(): void
    {
        $user = User::factory()->create();

        /** @var \App\Models\Recipe $recipe */
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $data = [
            'title' => 'Bolo De Brigadeiro',
            'description' => 'Receita atualizada',
            'ingredients' => ['Leite Condensado', 'Chocolate'],
            'steps' => "Misture\ne Cozinhe"
        ];

        $dto = RecipeDTO::fromArray($data);
        $service = new RecipeService();
        $updatedRecipe = $service->update($recipe, $dto);

        expect($updatedRecipe->title)->toBe('bolo de brigadeiro');
        expect($updatedRecipe->description)->toBe('Receita atualizada');
        expect($updatedRecipe->ingredients)->toHaveCount(2);
        expect($updatedRecipe->steps)->toHaveCount(2);
    }

    public function test_delete_recipe(): void
    {
        $user = User::factory()->create();

        /** @var \App\Models\Recipe $recipe */
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $service = new RecipeService();
        $result = $service->delete($recipe);

        expect($result)->toBeTrue();
        expect(Recipe::count())->toBe(0);
        expect(Recipe::withTrashed()->count())->toBe(1);
    }

    public function test_create_recipe_without_description(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $data = [
            'title' => 'Receita Sem Descrição',
            'description' => null,
            'ingredients' => ['Ingrediente 1'],
            'steps' => "Passo 1: Fazer algo",
        ];

        $dto = RecipeDTO::fromArray($data);
        $service = new RecipeService();
        $recipe = $service->create($dto);

        expect($recipe->description)->toBeNull();
        expect($recipe->ingredients)->toHaveCount(1);
    }

    public function test_create_recipe_sanitizes_ingredients(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $data = [
            'title' => 'Receita Teste',
            'description' => null,
            'ingredients' => [' Farinha ', 'açúcar', 'Farinha', 'Açúcar'],
            'steps' => "Passo 1: Teste",
        ];

        $dto = RecipeDTO::fromArray($data);
        $service = new RecipeService();
        $recipe = $service->create($dto);

        expect($recipe->ingredients)->toHaveCount(2);
        expect($recipe->ingredients)->toContain('Farinha');
        expect($recipe->ingredients)->toContain('Açúcar');
    }

    public function test_update_does_not_change_author(): void
    {
        $user = User::factory()->create();

        /** @var \App\Models\Recipe $recipe */
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $data = [
            'title' => 'Título Alterado',
            'description' => null,
            'ingredients' => ['Novo Ingrediente'],
            'steps' => "Novo Passo",
        ];

        $dto = RecipeDTO::fromArray($data);
        $service = new RecipeService();
        $updatedRecipe = $service->update($recipe, $dto);

        expect($updatedRecipe->user_id)->toBe($user->id);
    }

    public function test_service_creates_recipe_with_slug(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $data = [
            'title' => 'Receita com Título Longo',
            'description' => null,
            'ingredients' => ['Teste'],
            'steps' => "Passo 1: Teste",
        ];

        $dto = RecipeDTO::fromArray($data);
        $service = new RecipeService();

        $recipe = $service->create($dto);

        expect($recipe->slug)->toBe('receita-com-titulo-longo');
    }
}

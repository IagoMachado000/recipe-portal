<?php

namespace Tests\Feature\Recipe;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_recipe_and_redirects(): void
    {
        $user = User::factory()->create();

        $data = [
            'title' => 'Nova Receita',
            'description' => 'Descrição da receita',
            'ingredients' => ['Ingrediente 1', 'Ingrediente 2'],
            'steps' => "Passo 1: Fazer algo\nPasso 2: Finalizar",
        ];

        $response = $this->actingAs($user)
            ->post(route('recipes.store'), $data);

        $recipe = Recipe::first();

        $response->assertRedirect(route('recipes.show', ['recipe' => $recipe->id]));
        $response->assertSessionHas('success', 'Receita criada!');

        expect(Recipe::count())->toBe(1);
        expect(Recipe::first()->title)->toBe('nova receita');
    }

    public function test_store_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $data = [
            'title' => '',
            'ingredients' => [],
            'steps' => '',
        ];

        $response = $this->actingAs($user)
            ->post(route('recipes.store'), $data);

        $response->assertSessionHasErrors(['title', 'ingredients', 'steps']);

        expect(Recipe::count())->toBe(0);
    }

    public function test_store_validates_title_max_length(): void
    {
        $user = User::factory()->create();

        $data = [
            'title' => str_repeat('a', 121),
            'description' => null,
            'ingredients' => ['Teste'],
            'steps' => "Passo 1: Teste",
        ];

        $response = $this->actingAs($user)
            ->post(route('recipes.store'), $data);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_store_validates_unique_title(): void
    {
        $user = User::factory()->create();

        Recipe::factory()->create(['title' => 'Receita Existente', 'user_id' => $user->id]);

        $data = [
            'title' => 'Receita Existente',
            'description' => null,
            'ingredients' => ['Teste'],
            'steps' => "Passo 1: Teste",
        ];

        $response = $this->actingAs($user)
            ->post(route('recipes.store'), $data);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_store_requires_authentication(): void
    {
        $data = [
            'title' => 'Receita Teste',
            'description' => null,
            'ingredients' => ['Teste'],
            'steps' => "Passo 1: Teste",
        ];

        $response = $this->post(route('recipes.store'), $data);
        $response->assertRedirect(route('login'));

        expect(Recipe::count())->toBe(0);
    }

    public function test_update_recipe_and_redirects(): void
    {
        $user = User::factory()->create();

        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $data = [
            'title' => 'Receita Atualizada',
            'description' => 'Nova descrição',
            'ingredients' => ['Novo Ingrediente'],
            'steps' => "Novo Passo",
        ];

        $response = $this->actingAs($user)
            ->put(route('recipes.update', $recipe), $data);

        $response->assertRedirect(route('recipes.show', $recipe));
        $response->assertSessionHas('success', 'Receita atualizada!');
        $recipe->refresh();

        expect($recipe->title)->toBe('receita atualizada');
    }

    public function test_update_prevents_unauthorized_user(): void
    {
        $author = User::factory()->create();

        $otherUser = User::factory()->create();

        $recipe = Recipe::factory()->create(['user_id' => $author->id]);

        $data = [
            'title' => 'Título Alterado',
            'description' => null,
            'ingredients' => ['Teste'],
            'steps' => "Passo 1: Teste",
        ];

        $response = $this->actingAs($otherUser)
            ->put(route('recipes.update', $recipe), $data);

        $response->assertForbidden();

        expect($recipe->title)->not->toBe('Título Alterado');
    }

    public function test_delete_recipe_and_redirects(): void
    {
        $user = User::factory()->create();

        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->delete(route('recipes.destroy', $recipe));

        $response->assertRedirect(route('recipes.dashboard'));

        $response->assertSessionHas('success', 'Receita excluída!');

        expect(Recipe::count())->toBe(0);
        expect(Recipe::withTrashed()->count())->toBe(1);
    }

    public function test_delete_prevents_unauthorized_user(): void
    {
        $author = User::factory()->create();

        $otherUser = User::factory()->create();

        $recipe = Recipe::factory()->create(['user_id' => $author->id]);

        $response = $this->actingAs($otherUser)
            ->delete(route('recipes.destroy', $recipe));

        $response->assertForbidden();
        expect(Recipe::withTrashed()->count())->toBe(1);
    }
}

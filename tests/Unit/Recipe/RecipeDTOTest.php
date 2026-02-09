<?php

namespace Tests\Unit\Recipe;

use App\DTOs\RecipeDTO;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeDTOTest extends TestCase
{
    use RefreshDatabase;

    public function test_fromArray_creates_dto_correctly(): void
    {
        $data = [
            'title' => 'Bolo de Festa',
            'description' => 'Bolo decorado',
            'ingredients' => ['Farinha', 'Açúcar', 'Corante'],
            'steps' => "Misture\nAsse\nDecore",
        ];

        $dto = RecipeDTO::fromArray($data);

        expect($dto->title)->toBe('bolo de festa');
        expect($dto->description)->toBe('Bolo decorado');
        expect($dto->ingredients)->toHaveCount(3);
        expect($dto->steps)->toBeArray();
    }

    public function test_fromModel_creates_dto_correctly(): void
    {
        $user = User::factory()->create();

        /** @var \App\Models\Recipe $recipe */
        $recipe = Recipe::factory()->create([
            'title' => 'receita do model',
        ]);

        $dto = RecipeDTO::fromModel($recipe);

        expect($dto->title)->toBe($recipe->title);
        expect($dto->description)->toBe($recipe->description);
        expect($dto->ingredients)->toBeArray();
    }

    public function test_sanitizes_title_with_str_title(): void
    {
        $data = [
            'title' => '  Bolo   De   Chocolate  ',
            'description' => null,
            'ingredients' => ['teste'],
            'steps' => "teste",
        ];

        $dto = RecipeDTO::fromArray($data);

        expect($dto->title)->toBe('bolo de chocolate');
    }

    public function test_sanitizes_ingredients_removes_duplicates(): void
    {
        $data = [
            'title' => 'Teste',
            'description' => null,
            'ingredients' => ['Açúcar', 'açúcar', 'AÇÚCAR'],
            'steps' => "Teste",
        ];

        $dto = RecipeDTO::fromArray($data);

        expect($dto->ingredients)->toHaveCount(1);
        expect($dto->ingredients[0])->toBe('Açúcar');
    }

    public function test_sanitizes_steps_adds_prefix(): void
    {
        $data = [
            'title' => 'Teste',
            'description' => null,
            'ingredients' => ['Teste'],
            'steps' => "Misture os ingredientes\nAsse no forno",
        ];

        $dto = RecipeDTO::fromArray($data);

        expect($dto->steps[0])->toBe('Passo 1: Misture os ingredientes');
        expect($dto->steps[1])->toBe('Passo 2: Asse no forno');
    }
}

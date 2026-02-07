<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Rating;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = app(Faker::class);

        // 1. Criar 10 usuários base
        $users = User::factory(10)->create();

        // 2. Criar 10 receitas distribuídas entre os usuários
        $recipes = Recipe::factory(10)
            ->recycle($users) // Reutiliza os usuários criados
            ->create();

        // 3. Criar comentários (5-10 por receita)
        foreach ($recipes as $recipe) {
            $commentCount = $faker->numberBetween(5, 10);

            Comment::factory($commentCount)
                ->recycle($users)
                ->create(['recipe_id' => $recipe->id]);
        }

        // 4. Criar avaliações (10-20 por receita) respeitando constraint UNIQUE
        foreach ($recipes as $recipe) {
            $this->createRatingsForRecipe($recipe, $users, $faker);
        }

        // 5. Atualizar métricas das receitas
        $this->updateRecipeMetrics($recipes);
    }

    /**
     * Cria avaliações para uma receita específica respeitando a constraint UNIQUE
     */
    private function createRatingsForRecipe(Recipe $recipe, \Illuminate\Database\Eloquent\Collection $users, Faker $faker): void
    {
        $ratingCount = $faker->numberBetween(10, 20);

        // Seleciona usuários aleatórios sem repetição para esta receita
        $availableUsers = $users->random(min($ratingCount, $users->count()));

        foreach ($availableUsers as $user) {
            // Verifica se este usuário já avaliou esta receita
            $existingRating = Rating::where('recipe_id', $recipe->id)
                ->where('user_id', $user->id)
                ->exists();
            if (!$existingRating) {
                Rating::factory()->create([
                    'recipe_id' => $recipe->id,
                    'user_id' => $user->id,
                ]);
            }
        }
    }

    /**
     * Atualiza as métricas de avaliação das receitas
     */
    private function updateRecipeMetrics(\Illuminate\Database\Eloquent\Collection $recipes): void
    {
        foreach ($recipes as $recipe) {
            $ratings = Rating::where('recipe_id', $recipe->id)->get();

            if ($ratings->isNotEmpty()) {
                $avgRating = $ratings->avg('score');
                $ratingCount = $ratings->count();

                $recipe->update([
                    'rating_avg' => round($avgRating, 2),
                    'rating_count' => $ratingCount,
                ]);
            }
        }
    }
}

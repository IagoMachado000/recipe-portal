<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Rating;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se o DatabaseSeeder cria a quantidade correta de usuários
     */
    public function test_database_seeder_creates_correct_number_of_users(): void
    {
        // Executar o seeder
        $this->seed();

        // Verificar se 10 usuários foram criados
        $this->assertEquals(10, User::count());
    }

    /**
     * Testa se o DatabaseSeeder cria a quantidade correta de receitas
     */
    public function test_database_seeder_creates_correct_number_of_recipes(): void
    {
        // Executar o seeder
        $this->seed();

        // Verificar se 10 receitas foram criadas
        $this->assertEquals(10, Recipe::count());
    }

    /**
     * Testa se cada receita tem entre 5 e 10 comentários
     */
    public function test_database_seeder_creates_comments_within_expected_range(): void
    {
        // Executar o seeder
        $this->seed();

        // Obter todas as receitas
        $recipes = Recipe::all();

        // Verificar cada receita tem entre 5 e 10 comentários
        foreach ($recipes as $recipe) {
            $commentCount = $recipe->comments()->count();
            $this->assertGreaterThanOrEqual(5, $commentCount);
            $this->assertLessThanOrEqual(10, $commentCount);
        }
    }

    /**
     * Testa se cada receita tem entre 10 e 20 avaliações
     */
    public function test_database_seeder_creates_ratings_within_expected_range(): void
    {
        // Executar o seeder
        $this->seed();

        // Obter todas as receitas
        $recipes = Recipe::all();

        // Verificar cada receita tem entre 10 e 20 avaliações
        foreach ($recipes as $recipe) {
            $ratingCount = $recipe->ratings()->count();
            $this->assertGreaterThanOrEqual(10, $ratingCount);
            $this->assertLessThanOrEqual(20, $ratingCount);
        }
    }

    /**
     * Testa se a constraint unique (recipe_id, user_id) é respeitada nas avaliações
     */
    public function test_database_seeder_respects_unique_rating_constraint(): void
    {
        // Executar o seeder
        $this->seed();

        // Verificar se não há avaliações duplicadas (recipe_id, user_id)
        $duplicates = Rating::select(['recipe_id', 'user_id'])
            ->groupBy(['recipe_id', 'user_id'])
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $this->assertEquals(0, $duplicates, 'Avaliações duplicadas encontradas!');
    }

    /**
     * Testa se as médias de avaliação foram calculadas corretamente
     */
    public function test_database_seeder_calculates_rating_averages_correctly(): void
    {
        // Executar o seeder
        $this->seed();

        // Obter todas as receitas
        $recipes = Recipe::all();

        // Verificar cada receita tem média calculada
        foreach ($recipes as $recipe) {
            // Se a receita tem avaliações, a média deve ser > 0
            if ($recipe->ratings()->count() > 0) {
                $this->assertGreaterThan(0, $recipe->rating_avg);
                $this->assertEquals($recipe->ratings()->count(), $recipe->rating_count);
            }
        }
    }

    /**
     * Testa se os relacionamentos estão funcionando corretamente
     */
    public function test_database_seeder_relationships_are_working(): void
    {
        // Executar o seeder
        $this->seed();

        // Testar relacionamento User -> Recipe
        $user = User::first();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->recipes);

        // Testar relacionamento Recipe -> User
        $recipe = Recipe::first();
        $this->assertInstanceOf(User::class, $recipe->user);

        // Testar relacionamento Recipe -> Comments
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $recipe->comments);

        // Testar relacionamento Recipe -> Ratings
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $recipe->ratings);

        // Testar relacionamento Comment -> User e Recipe
        $comment = Comment::first();
        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertInstanceOf(Recipe::class, $comment->recipe);

        // Testar relacionamento Rating -> User e Recipe
        $rating = Rating::first();
        $this->assertInstanceOf(User::class, $rating->user);
        $this->assertInstanceOf(Recipe::class, $rating->recipe);
    }

    /**
     * Testa se os dados gerados são realistas e válidos
     */
    public function test_database_seeder_generates_realistic_data(): void
    {
        // Executar o seeder
        $this->seed();

        // Verificar se receitas têm títulos válidos
        $recipes = Recipe::all();
        foreach ($recipes as $recipe) {
            $this->assertNotEmpty($recipe->title);
            $this->assertLessThanOrEqual(120, strlen($recipe->title));
            $this->assertIsArray($recipe->ingredients);
            $this->assertNotEmpty($recipe->ingredients);
        }

        // Verificar se comentários têm conteúdo válido
        $comments = Comment::all();
        foreach ($comments as $comment) {
            $this->assertNotEmpty($comment->body);
            $this->assertLessThanOrEqual(1000, strlen($comment->body));
        }

        // Verificar se avaliações têm scores válidos
        $ratings = Rating::all();
        foreach ($ratings as $rating) {
            $this->assertGreaterThanOrEqual(1, $rating->score);
            $this->assertLessThanOrEqual(5, $rating->score);
        }
    }
}

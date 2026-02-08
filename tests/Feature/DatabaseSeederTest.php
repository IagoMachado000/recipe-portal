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
        expect(User::count())->toBe(10);
    }

    /**
     * Testa se o DatabaseSeeder cria a quantidade correta de receitas
     */
    public function test_database_seeder_creates_correct_number_of_recipes(): void
    {
        // Executar o seeder
        $this->seed();

        // Verificar se 10 receitas foram criadas
        expect(Recipe::count())->toBe(10);
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
            expect($commentCount)->toBeGreaterThanOrEqual(5);
            expect($commentCount)->toBeLessThanOrEqual(10);
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
            expect($ratingCount)->toBeGreaterThanOrEqual(10);
            expect($ratingCount)->toBeLessThanOrEqual(20);
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

        expect($duplicates)->toBe(0, 'Avaliações duplicadas encontradas!');
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
                expect($recipe->rating_avg)->toBeGreaterThan(0);
                expect($recipe->rating_count)->toBe($recipe->ratings()->count());
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
        expect($user->recipes)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);

        // Testar relacionamento Recipe -> User
        $recipe = Recipe::first();
        expect($recipe->user)->toBeInstanceOf(User::class);

        // Testar relacionamento Recipe -> Comments
        expect($recipe->comments)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);

        // Testar relacionamento Recipe -> Ratings
        expect($recipe->ratings)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);

        // Testar relacionamento Comment -> User e Recipe
        $comment = Comment::first();
        expect($comment->user)->toBeInstanceOf(User::class);
        expect($comment->recipe)->toBeInstanceOf(Recipe::class);

        // Testar relacionamento Rating -> User e Recipe
        $rating = Rating::first();
        expect($rating->user)->toBeInstanceOf(User::class);
        expect($rating->recipe)->toBeInstanceOf(Recipe::class);
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
            expect($recipe->title)->not->toBeEmpty();
            expect(strlen($recipe->title))->toBeLessThanOrEqual(120);
            expect($recipe->ingredients)->toBeArray();
            expect($recipe->ingredients)->not->toBeEmpty();
        }

        // Verificar se comentários têm conteúdo válido
        $comments = Comment::all();
        foreach ($comments as $comment) {
            expect($comment->body)->not->toBeEmpty();
            expect(strlen($comment->body))->toBeLessThanOrEqual(1000);
        }

        // Verificar se avaliações têm scores válidos
        $ratings = Rating::all();
        foreach ($ratings as $rating) {
            expect($rating->score)->toBeGreaterThanOrEqual(1);
            expect($rating->score)->toBeLessThanOrEqual(5);
        }
    }
}

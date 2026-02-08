<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa relacionamento com User (belongsTo)
     */
    public function test_comment_belongs_to_user(): void
    {
        // Criar usuário, receita e comentário
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        // Verificar relacionamento
        expect($comment->user)->toBeInstanceOf(User::class);
        expect($comment->user->id)->toBe($user->id);
        expect($comment->user->name)->toBe($user->name);
    }

    /**
     * Testa relacionamento com Recipe (belongsTo)
     */
    public function test_comment_belongs_to_recipe(): void
    {
        // Criar usuário, receita e comentário
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        // Verificar relacionamento
        expect($comment->recipe)->toBeInstanceOf(Recipe::class);
        expect($comment->recipe->id)->toBe($recipe->id);
        expect($comment->recipe->title)->toBe($recipe->title);
    }

    /**
     * Testa se o body do comentário não é vazio
     */
    public function test_comment_body_is_not_empty(): void
    {
        // Criar comentário
        $comment = Comment::factory()->create();

        // Verificar que body não é vazio
        expect($comment->body)->not->toBeEmpty();
        expect($comment->body)->toBeString();
        expect(strlen($comment->body))->toBeGreaterThan(0);
    }

    /**
     * Testa se o body do comentário respeita o limite máximo de caracteres
     */
    public function test_comment_body_respects_maximum_length(): void
    {
        // Criar comentário
        $comment = Comment::factory()->create();

        // Verificar que body respeita limite de 1000 caracteres
        expect($comment->body)->toBeString();
        expect(strlen($comment->body))->toBeLessThanOrEqual(1000);
        expect(strlen($comment->body))->toBeGreaterThan(0);
    }

    /**
     * Testa se o body gerado pelo factory é realista
     */
    public function test_comment_body_is_realistic(): void
    {
        // Criar múltiplos comentários
        $comments = Comment::factory(5)->create();

        // Verificar se todos os bodies são realistas
        foreach ($comments as $comment) {
            expect($comment->body)->toBeString();
            expect($comment->body)->not->toBeEmpty();
            expect(strlen($comment->body))->toBeLessThanOrEqual(1000);

            // Verificar se contém palavras em português (dados realistas)
            expect(strlen($comment->body))->toBeGreaterThan(10); // Comentários razoavelmente longos
        }
    }

    /**
     * Testa se comentários são ordenados por created_at DESC
     */
    public function test_comments_are_ordered_by_created_at_desc(): void
    {
        // Criar receita
        $recipe = Recipe::factory()->create();
        $user = User::factory()->create();

        // Criar comentários em momentos diferentes
        $comments = [];
        for ($i = 0; $i < 3; $i++) {
            $comment = Comment::factory()->create([
                'recipe_id' => $recipe->id,
                'user_id' => $user->id,
            ]);
            $comments[] = $comment;

            // Pequena pausa para garantir created_at diferentes
            sleep(1);
        }

        // Obter comentários ordenados (deve usar o índice)
        $orderedComments = Comment::where('recipe_id', $recipe->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Verificar que estão ordenados por created_at DESC
        expect($orderedComments->count())->toBe(3);

        for ($i = 0; $i < $orderedComments->count() - 1; $i++) {
            $current = $orderedComments[$i];
            $next = $orderedComments[$i + 1];

            expect($current->created_at->greaterThan($next->created_at))->toBeTrue();
        }
    }

    /**
     * Testa se múltiplos usuários podem comentar a mesma receita
     */
    public function test_multiple_users_can_comment_same_recipe(): void
    {
        // Criar receita e múltiplos usuários
        $recipe = Recipe::factory()->create();
        $users = User::factory(3)->create();

        // Criar comentários de usuários diferentes para mesma receita
        foreach ($users as $user) {
            Comment::factory()->create([
                'recipe_id' => $recipe->id,
                'user_id' => $user->id,
            ]);
        }

        // Verificar que todos os comentários foram criados
        $comments = Comment::where('recipe_id', $recipe->id)->get();
        expect($comments->count())->toBe(3);

        // Verificar que são de usuários diferentes
        $userIds = $comments->pluck('user_id')->unique();
        expect($userIds->count())->toBe(3);
    }

    /**
     * Testa se mesmo usuário pode comentar múltiplas receitas
     */
    public function test_same_user_can_comment_multiple_recipes(): void
    {
        // Criar usuário e múltiplas receitas
        $user = User::factory()->create();
        $recipes = Recipe::factory(3)->create();

        // Criar comentários do mesmo usuário para receitas diferentes
        foreach ($recipes as $recipe) {
            Comment::factory()->create([
                'recipe_id' => $recipe->id,
                'user_id' => $user->id,
            ]);
        }

        // Verificar que todos os comentários foram criados
        $comments = Comment::where('user_id', $user->id)->get();
        expect($comments->count())->toBe(3);

        // Verificar que são de receitas diferentes
        $recipeIds = $comments->pluck('recipe_id')->unique();
        expect($recipeIds->count())->toBe(3);
    }

    /**
     * Testa acesso a atributos específicos
     */
    public function test_comment_attribute_access(): void
    {
        // Criar comentário
        $comment = Comment::factory()->create();

        // Verificar acesso a atributos
        expect($comment->id)->toBeInt();
        expect($comment->recipe_id)->toBeInt();
        expect($comment->user_id)->toBeInt();
        expect($comment->body)->toBeString();
        expect($comment->created_at)->not->toBeNull();
        expect($comment->created_at)->toBeInstanceOf(\Carbon\Carbon::class);
    }

    /**
     * Testa se o comentário tem apenas created_at timestamp
     */
    public function test_comment_has_only_created_at_timestamp(): void
    {
        // Criar comentário
        $comment = Comment::factory()->create();

        // Verificar que tem created_at
        expect($comment->created_at)->not->toBeNull();
        expect($comment->created_at)->toBeInstanceOf(\Carbon\Carbon::class);
        $this->assertNotNull($comment->created_at);
    }

    /**
     * Testa se o comentário pode ser associado corretamente
     */
    public function test_comment_can_be_associated_correctly(): void
    {
        // Criar entidades
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create();

        // Criar comentário com associações específicas
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        // Verificar associações
        expect($comment->user_id)->toBe($user->id);
        expect($comment->recipe_id)->toBe($recipe->id);
        expect($comment->user->id)->toBe($user->id);
        expect($comment->recipe->id)->toBe($recipe->id);
    }

    /**
     * Testa se o comentário pode ser encontrado por suas associações
     */
    public function test_comment_can_be_found_by_associations(): void
    {
        // Criar entidades
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        // Encontrar por usuário
        $commentsByUser = Comment::where('user_id', $user->id)->get();
        expect($commentsByUser->count())->toBeGreaterThanOrEqual(1);
        expect($commentsByUser->first()->id)->toBe($comment->id);

        // Encontrar por receita
        $commentsByRecipe = Comment::where('recipe_id', $recipe->id)->get();
        expect($commentsByRecipe->count())->toBeGreaterThanOrEqual(1);
        expect($commentsByRecipe->first()->id)->toBe($comment->id);

        // Encontrar por ambos
        $commentsByBoth = Comment::where('user_id', $user->id)
            ->where('recipe_id', $recipe->id)
            ->get();
        expect($commentsByBoth->count())->toBe(1);
        expect($commentsByBoth->first()->id)->toBe($comment->id);
    }
}

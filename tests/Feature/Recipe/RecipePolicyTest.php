<?php

namespace Tests\Feature\Recipe;

use App\Models\Recipe;
use App\Models\User;
use App\Policies\RecipePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_update_recipe(): void
    {
        $user = User::factory()->create();

        /** @var \App\Models\Recipe $recipe */
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $policy = new RecipePolicy();

        expect($policy->update($user, $recipe))->toBeTrue();
    }

    public function test_non_author_cannot_update_recipe(): void
    {
        $author = User::factory()->create();

        $otherUser = User::factory()->create();

        /** @var \App\Models\Recipe $recipe */
        $recipe = Recipe::factory()->create(['user_id' => $author->id]);

        $policy = new RecipePolicy();

        expect($policy->update($otherUser, $recipe))->toBeFalse();
    }

    public function test_author_can_delete_recipe(): void
    {
        $user = User::factory()->create();

        /** @var \App\Models\Recipe $recipe */
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $policy = new RecipePolicy();

        expect($policy->delete($user, $recipe))->toBeTrue();
    }

    public function test_non_author_cannot_delete_recipe(): void
    {
        $author = User::factory()->create();

        $otherUser = User::factory()->create();

        /** @var \App\Models\Recipe $recipe */
        $recipe = Recipe::factory()->create(['user_id' => $author->id]);

        $policy = new RecipePolicy();

        expect($policy->delete($otherUser, $recipe))->toBeFalse();
    }
}

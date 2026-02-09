<?php

namespace Tests\Unit\Recipe;

use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request as HttpRequest;
use Tests\TestCase;

class RecipeFormRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validateFormRequest(object $formRequest, array $data, array $routeParams = [])
    {
        if (!empty($routeParams)) {
            // cria um request HTTP fake só para binding da rota
            $http = HttpRequest::create('/recipes/1', 'PUT', $data);

            $route = new Route(['PUT'], '/recipes/{recipe}', fn() => null);

            // bind() é o que evita o "Route is not bound"
            $route->bind($http);

            // injeta o model (ou id) que seu rules() espera em route('recipe')
            if (isset($routeParams['recipe'])) {
                $route->setParameter('recipe', $routeParams['recipe']);
            }

            $formRequest->setRouteResolver(fn() => $route);
        }

        return Validator::make($data, $formRequest->rules(), $formRequest->messages(), $formRequest->attributes());
    }

    public function test_store_request_validates_title_required(): void
    {
        $request = new StoreRecipeRequest();

        $validator = $this->validateFormRequest($request, [
            'title' => '',
            'description' => null,
            'ingredients' => ['Teste'],
            'steps' => "Passo 1: Teste",
        ]);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    }

    public function test_store_request_validates_title_min_3(): void
    {
        $request = new StoreRecipeRequest();

        $validator = $this->validateFormRequest($request, [
            'title' => 'ab',
            'description' => null,
            'ingredients' => ['Teste'],
            'steps' => "Passo 1: Teste",
        ]);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    }

    public function test_store_request_validates_title_max_120(): void
    {
        $request = new StoreRecipeRequest();

        $validator = $this->validateFormRequest($request, [
            'title' => str_repeat('a', 121),
            'description' => null,
            'ingredients' => ['Teste'],
            'steps' => "Passo 1: Teste",
        ]);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    }

    public function test_store_request_validates_ingredients_required(): void
    {
        $request = new StoreRecipeRequest();

        $validator = $this->validateFormRequest($request, [
            'title' => 'Título Válido',
            'description' => null,
            'ingredients' => [],
            'steps' => "Passo 1: Teste",
        ]);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('ingredients'))->toBeTrue();
    }

    public function test_store_request_validates_ingredients_max_20(): void
    {
        $request = new StoreRecipeRequest();

        $validator = $this->validateFormRequest($request, [
            'title' => 'Título Válido',
            'description' => null,
            'ingredients' => array_fill(0, 21, 'Ingrediente'),
            'steps' => "Passo 1: Teste",
        ]);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('ingredients'))->toBeTrue();
    }

    public function test_store_request_validates_steps_required(): void
    {
        $request = new StoreRecipeRequest();

        $validator = $this->validateFormRequest($request, [
            'title' => 'Título Válido',
            'description' => null,
            'ingredients' => ['Teste'],
            'steps' => '',
        ]);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('steps'))->toBeTrue();
    }

    public function test_store_request_validates_steps_min_10(): void
    {
        $request = new StoreRecipeRequest();

        $validator = $this->validateFormRequest($request, [
            'title' => 'Título Válido',
            'description' => null,
            'ingredients' => ['Teste'],
            'steps' => 'Curto',
        ]);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('steps'))->toBeTrue();
    }

    public function test_store_request_validates_description_max_500(): void
    {
        $request = new StoreRecipeRequest();

        $validator = $this->validateFormRequest($request, [
            'title' => 'Título Válido',
            'description' => str_repeat('a', 501),
            'ingredients' => ['Teste'],
            'steps' => "Passo 1: " . str_repeat('a', 10),
        ]);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('description'))->toBeTrue();
    }

    public function test_update_request_allows_same_title(): void
    {
        $user = User::factory()->create();

        $recipe = Recipe::factory()->create([
            'title' => 'Título Original',
            'user_id' => $user->id,
        ]);

        $request = new UpdateRecipeRequest();

        $validator = $this->validateFormRequest(
            $request,
            [
                'title' => 'Título Original',
                'description' => null,
                'ingredients' => ['Teste'],
                'steps' => "Passo 1: Teste",
            ],
            ['recipe' => $recipe]
        );

        expect($validator->fails())->toBeFalse();
        expect($validator->errors()->has('title'))->toBeFalse();
    }

    public function test_update_request_rejects_duplicate_title(): void
    {
        $user = User::factory()->create();

        Recipe::factory()->create([
            'title' => 'Título Existente',
            'user_id' => $user->id,
        ]);

        $recipeToUpdate = Recipe::factory()->create([
            'title' => 'Título Para Atualizar',
            'user_id' => $user->id,
        ]);

        $request = new UpdateRecipeRequest();

        $validator = $this->validateFormRequest(
            $request,
            [
                'title' => 'Título Existente',
                'description' => null,
                'ingredients' => ['Teste'],
                'steps' => "Passo 1: Teste",
            ],
            ['recipe' => $recipeToUpdate]
        );

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    }
}

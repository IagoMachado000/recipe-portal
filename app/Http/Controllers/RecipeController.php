<?php

namespace App\Http\Controllers;

use App\DTOs\RecipeDTO;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Models\Recipe;
use App\Services\RecipeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecipeController extends Controller
{
    public function __construct(
        private RecipeService $recipeService
    ) {}

    /**
     * Display a listing of the resource private.
     */
    public function dashboard()
    {
        $recipes = $this->recipeService->getUserRecipes(Auth::user());

        return view('recipes.dashboard', compact('recipes'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recipes = $this->recipeService->getPublishedRecipes();
        return view('recipes.index', compact('recipes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('recipes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecipeRequest $request)
    {
        $dto = RecipeDTO::fromArray([
            ...$request->validated(),
            'user_id' => (int) Auth::id(),
        ]);

        $recipe = $this->recipeService->create($dto);

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Receita criada!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Recipe $recipe)
    {
        return view('recipes.show', compact('recipe'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recipe $recipe)
    {
        $ingredients = old('ingredients', $recipe->ingredients ?? []);
        $ingredients = $ingredients ?: [''];

        $steps = old('steps');

        if ($steps === null) {
            $stepsArray = is_array($recipe->steps) ? $recipe->steps : [];

            $stepsArray = array_map(function (string $step) {
                // Remove "Passo X:" do início
                return preg_replace('/^Passo\s+\d+\s*:\s*/i', '', $step);
            }, $stepsArray);

            $steps = implode("\n", $stepsArray);
        }

        return view('recipes.edit', compact('recipe', 'ingredients', 'steps'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $dto = RecipeDTO::fromArray([
            ...$request->validated(),
            'user_id' => (int) $recipe->user_id,
        ]);

        $recipe = $this->recipeService->update($recipe, $dto);

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Receita atualizada!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $this->authorize('delete', $recipe);

        $this->recipeService->delete($recipe);

        return redirect()->route('recipes.dashboard')
            ->with('success', 'Receita excluída!');
    }
}

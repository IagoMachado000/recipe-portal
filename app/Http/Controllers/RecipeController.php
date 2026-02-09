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
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recipe $recipe)
    {
        //
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

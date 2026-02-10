<?php

namespace App\Http\Controllers;

use App\DTOs\RatingDTO;
use App\Http\Requests\StoreRatingRequest;
use App\Models\Rating;
use App\Services\RatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RatingController extends Controller
{
    public function __construct(
        private RatingService $ratingService
    ) {}

    public function store(StoreRatingRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $dto = RatingDTO::fromArray([
                'recipe_id' => $request->validated('recipe_id'),
                'user_id' => Auth::id(),
                'score' => $request->validated('score'),
            ]);

            $rating = $this->ratingService->rate($dto);

            // Eager loading para resposta
            $rating->load(['recipe.user']);

            // Resposta AJAX (para updates sem reload)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Avaliação registrada com sucesso!',
                    'data' => [
                        'rating_id' => $rating->id,
                        'score' => $rating->score,
                        'new_average' => number_format($rating->recipe->fresh()->rating_avg, 2),
                        'total_ratings' => $rating->recipe->fresh()->rating_count,
                        'was_created' => $rating->wasRecentlyCreated,
                    ]
                ]);
            }
            return redirect()->back()
                ->with('success', 'Avaliação registrada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao registrar avaliação', [
                'error' => $e->getMessage(),
                'recipe_id' => $request->recipe_id,
                'user_id' => Auth::id(),
                'score' => $request->score,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao registrar avaliação. Tente novamente.',
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Erro ao registrar avaliação. Tente novamente.')
                ->withInput();
        }
    }
}

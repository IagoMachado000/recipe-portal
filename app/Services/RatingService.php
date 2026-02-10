<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\RatingDTO;
use App\Models\Rating;
use App\Models\Recipe;
use App\Notifications\NewRatingNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class RatingService
{
    public function rate(RatingDTO $dto): Rating
    {
        return DB::transaction(function () use ($dto) {
            // updateOrCreate respeita constraint unique automaticamente
            $rating = Rating::updateOrCreate(
                ['recipe_id' => $dto->recipeId, 'user_id' => $dto->userId],
                ['score' => $dto->score]
            );

            // Recalcular médias da receita com 2 casas decimais
            $this->updateRecipeRatings($dto->recipeId);

            // Enviar notificação (apenas para avaliações novas)
            if ($rating->wasRecentlyCreated) {
                $this->notifyRecipeAuthor($rating);
            }

            Log::info('Avaliação registrada com sucesso', [
                'rating_id' => $rating->id,
                'recipe_id' => $rating->recipe_id,
                'user_id' => $rating->user_id,
                'score' => $rating->score,
                'was_created' => $rating->wasRecentlyCreated,
            ]);

            return $rating;
        });
    }

    private function updateRecipeRatings(int $recipeId): void
    {
        $stats = Rating::where('recipe_id', $recipeId)
            ->selectRaw('CAST(CAST(AVG(score) AS DECIMAL(10,2)) AS FLOAT) as avg_score, COUNT(*) as total_ratings')
            ->first();

        if ($stats->total_ratings > 0) {
            Recipe::where('id', $recipeId)->update([
                'rating_avg' => round($stats->avg_score, 2),
                'rating_count' => $stats->total_ratings,
            ]);
        }
    }

    private function notifyRecipeAuthor(Rating $rating): void
    {
        try {
            $recipeAuthor = $rating->recipe->user;

            // Não enviar notificação para autor avaliar própria receita
            if ($rating->user_id === $recipeAuthor->id) {
                return;
            }

            $recipeAuthor->notify(new NewRatingNotification($rating));

            Log::info('Notificação de nova avaliação enviada', [
                'recipe_author_id' => $recipeAuthor->id,
                'rating_author_id' => $rating->user_id,
                'rating_id' => $rating->id,
                'recipe_id' => $rating->recipe_id,
                'score' => $rating->score,
            ]);
        } catch (\Exception $e) {
            // Falha na notificação não deve quebrar o flow
            Log::error('Falha ao enviar notificação de avaliação', [
                'error' => $e->getMessage(),
                'rating_id' => $rating->id,
            ]);
        }
    }
}

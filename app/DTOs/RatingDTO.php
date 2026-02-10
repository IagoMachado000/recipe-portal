<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Rating;

class RatingDTO
{
    public function __construct(
        public int $recipeId,
        public int $userId,
        public int $score
    ) {
        $this->score = $this->sanitizeScore($score);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            recipeId: (int) $data['recipe_id'],
            userId: (int) $data['user_id'],
            score: (int) $data['score'],
        );
    }

    public static function fromModel(Rating $rating): self
    {
        return new self(
            recipeId: $rating->recipe_id,
            userId: $rating->user_id,
            score: $rating->score,
        );
    }

    public function toArray(): array
    {
        return [
            'recipe_id' => $this->recipeId,
            'user_id' => $this->userId,
            'score' => $this->score,

        ];
    }
    private function sanitizeScore(int $score): int
    {
        // Garante que score esteja no intervalo 1-5
        return max(1, min(5, $score));
    }
}

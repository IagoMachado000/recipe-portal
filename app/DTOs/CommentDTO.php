<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Comment;

class CommentDTO
{
    public function __construct(
        public int $recipeId,
        public int $userId,
        public string $body
    ) {
        $this->body = $this->sanitizeBody($body);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            recipeId: (int) $data['recipe_id'],
            userId: (int) $data['user_id'],
            body: $data['body'],
        );
    }

    public static function fromModel(Comment $comment): self
    {
        return new self(
            recipeId: $comment->recipe_id,
            userId: $comment->user_id,
            body: $comment->body,
        );
    }

    public function toArray(): array
    {
        return [
            'recipe_id' => $this->recipeId,
            'user_id' => $this->userId,
            'body' => $this->body,
        ];
    }

    private function sanitizeBody(string $body): string
    {
        // Remover HTML tags (prevenção XSS)
        $body = strip_tags($body);

        // Normalizar whitespace
        $body = preg_replace('/\s+/', ' ', $body);

        // Trim espaços
        $body = trim($body);

        return $body;
    }
}

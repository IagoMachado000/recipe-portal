<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CommentDTO;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\NewCommentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

// Import para notificação (criaremos depois)
// use App\Notifications\NewCommentNotification;
class CommentService
{
    public function create(CommentDTO $dto): Comment
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();

            // Validar se usuário está autenticado (redundância de segurança)
            if (!Auth::check()) {
                throw new \RuntimeException('Usuário não autenticado.');
            }

            // Criar comentário
            $comment = Comment::create($data);

            // Carregar relacionamentos para notificação
            $comment->load(['recipe.user']);

            // Enviar notificação para autor da receita (se não for o mesmo usuário)
            if ($comment->recipe->user_id !== $comment->user_id) {
                $this->notifyRecipeAuthor($comment);
            }

            Log::info('Comentário criado com sucesso', [
                'comment_id' => $comment->id,
                'recipe_id' => $comment->recipe_id,
                'user_id' => $comment->user_id,
                'recipe_author_id' => $comment->recipe->user_id,
            ]);

            return $comment;
        });
    }

    public function delete(Comment $comment): bool
    {
        return DB::transaction(function () use ($comment) {
            // Validar autorização
            if ($comment->user_id !== Auth::id()) {
                throw new \RuntimeException('Usuário não pode excluir este comentário.');
            }

            $comment->delete();

            Log::info('Comentário excluído com sucesso', [
                'comment_id' => $comment->id,
                'recipe_id' => $comment->recipe_id,
                'user_id' => $comment->user_id,
            ]);
            return true;
        });
    }

    private function notifyRecipeAuthor(Comment $comment): void
    {
        try {
            $recipeAuthor = $comment->recipe->user;
            $commentAuthor = $comment->user;

            $recipeAuthor->notify(new NewCommentNotification($comment));

            Log::info('Notificação de novo comentário enviada', [
                'recipe_author_id' => $recipeAuthor->id,
                'comment_author_id' => $commentAuthor->id,
                'comment_id' => $comment->id,
                'recipe_id' => $comment->recipe_id,
            ]);
        } catch (\Exception $e) {
            // Log de falha na notificação não deve quebrar o flow
            Log::error('Falha ao enviar notificação de comentário', [
                'error' => $e->getMessage(),
                'comment_id' => $comment->id,
            ]);
        }
    }
}

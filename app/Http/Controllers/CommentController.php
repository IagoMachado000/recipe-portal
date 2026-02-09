<?php

namespace App\Http\Controllers;

use App\DTOs\CommentDTO;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function __construct(
        private CommentService $commentService
    ) {}

    public function store(StoreCommentRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $dto = CommentDTO::fromArray([
                'recipe_id' => $request->validated('recipe_id'),
                'user_id' => Auth::id(),
                'body' => $request->validated('body'),
            ]);

            $comment = $this->commentService->create($dto);

            // Eager loading para resposta
            $comment->load('user:id,name');

            // Resposta AJAX (para updates sem reload)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comentário adicionado com sucesso!',
                    'comment' => [
                        'id' => $comment->id,
                        'body' => $comment->body,
                        'user_name' => $comment->user->name,
                        'created_at' => $comment->created_at->format('d/m/Y H:i'),
                        'can_delete' => $comment->user_id === Auth::id(),
                    ]
                ]);
            }

            return redirect()->back()
                ->with('success', 'Comentário adicionado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar comentário', [
                'error' => $e->getMessage(),
                'recipe_id' => $request->recipe_id,
                'user_id' => Auth::id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao adicionar comentário. Tente novamente.',
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Erro ao adicionar comentário. Tente novamente.')
                ->withInput();
        }
    }

    public function destroy(Comment $comment): JsonResponse|RedirectResponse
    {
        try {
            $this->commentService->delete($comment);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comentário excluído com sucesso!',
                ]);
            }

            return redirect()->back()
                ->with('success', 'Comentário excluído com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir comentário', [
                'error' => $e->getMessage(),
                'comment_id' => $comment->id,
                'user_id' => Auth::id(),
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao excluir comentário. Tente novamente.',
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Erro ao excluir comentário. Tente novamente.');
        }
    }
}

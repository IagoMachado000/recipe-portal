<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Comment $comment)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Novo comentário na sua receita',
            'message' => "{$this->comment->user->name} comentou em \"{$this->comment->recipe->title}\"",

            // Dados para link/actions
            'comment_id' => $this->comment->id,
            'recipe_id' => $this->comment->recipe_id,
            'comment_author_id' => $this->comment->user_id,
            'comment_author_name' => $this->comment->user->name,

            // Metadados para UI
            'type' => 'new_comment',
            'icon' => '💬',
            'url' => route('recipes.show', $this->comment->recipe->slug) . '#comment-' . $this->comment->id,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

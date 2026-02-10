<?php

namespace App\Notifications;

use App\Models\Rating;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRatingNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Rating $rating)
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
            'title' => 'Nova avaliação na sua receita',
            'message' => "{$this->rating->user->name} avaliou \"{$this->rating->recipe->title}\" com {$this->rating->score} estrela(s)",
            // Dados para link/actions
            'rating_id' => $this->rating->id,
            'recipe_id' => $this->rating->recipe_id,
            'rating_author_id' => $this->rating->user_id,
            'rating_author_name' => $this->rating->user->name,
            'score' => $this->rating->score,
            // Metadados para UI
            'type' => 'new_rating',
            'icon' => '⭐',
            'url' => route('recipes.show', $this->rating->recipe->slug) . '#rating-' . $this->rating->id,
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

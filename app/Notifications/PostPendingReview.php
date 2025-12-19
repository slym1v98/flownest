<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostPendingReview extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Post $post
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $title = is_array($this->post->title) 
            ? ($this->post->title[config('app.locale')] ?? $this->post->title[config('app.fallback_locale')] ?? 'Untitled')
            : $this->post->title;

        return (new MailMessage)
            ->subject('Post Pending Review: ' . $title)
            ->line('A new post has been submitted for review.')
            ->line('Title: ' . $title)
            ->line('Author: ' . $this->post->user->name)
            ->action('Review Post', url('/admin/posts/' . $this->post->id . '/edit'))
            ->line('Please review and approve or reject this post.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = is_array($this->post->title) 
            ? ($this->post->title[config('app.locale')] ?? $this->post->title[config('app.fallback_locale')] ?? 'Untitled')
            : $this->post->title;

        return [
            'post_id' => $this->post->id,
            'title' => $title,
            'author' => $this->post->user->name,
            'message' => 'A new post has been submitted for review.',
            'action_url' => url('/admin/posts/' . $this->post->id . '/edit'),
        ];
    }
}

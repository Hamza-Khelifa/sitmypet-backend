<?php

declare(strict_types=1);

namespace App\Domains\Profile\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $reason)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Identity Verification Rejected')
                    ->line('Your recent verification document submission was reviewed and rejected.')
                    ->line('Reason: ' . $this->reason)
                    ->line('Please upload a new, clear document meeting our guidelines.')
                    ->action('Go to Dashboard', url('/'));
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Notifications;

use App\Domains\Marketplace\Entities\Booking;
use App\Domains\Notifications\Channels\FirebasePushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Booking $booking
    ) {}

    public function via($notifiable): array
    {
        return [FirebasePushChannel::class];
    }

    public function toFirebasePush($notifiable): array
    {
        $role = $notifiable->id === $this->booking->sitter_id ? 'Sitter' : 'Owner';
        
        return [
            'notification' => [
                'title' => 'Booking Confirmed!',
                'body' => "Your booking has been confirmed and escrow is locked.",
            ],
            'data' => [
                'type' => 'booking_confirmed',
                'booking_id' => $this->booking->id,
                'demand_id' => $this->booking->demand_id,
                'role' => $role,
            ],
        ];
    }
}

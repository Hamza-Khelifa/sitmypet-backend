<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Notifications;

use App\Domains\Marketplace\Entities\Bid;
use App\Domains\Notifications\Channels\FirebasePushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewBidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Bid $bid
    ) {}

    public function via($notifiable): array
    {
        return [FirebasePushChannel::class];
    }

    public function toFirebasePush($notifiable): array
    {
        return [
            'notification' => [
                'title' => 'New Bid Received!',
                'body' => "A sitter has placed a bid of {$this->bid->proposed_rate} on your demand.",
            ],
            'data' => [
                'type' => 'new_bid',
                'bid_id' => $this->bid->id,
                'demand_id' => $this->bid->demand_id,
            ],
        ];
    }
}

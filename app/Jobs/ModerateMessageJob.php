<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\AiGateway\Actions\ModerateContentAction;
use App\Domains\AiGateway\DTOs\ModerateContentDTO;
use App\Domains\Communication\Entities\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ModerateMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Message $message;

    /**
     * Create a new job instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(ModerateContentAction $moderateContentAction): void
    {
        // Don't moderate empty or system messages
        if (!$this->message->content || $this->message->type->value !== 'text') {
            return;
        }

        $dto = new ModerateContentDTO(
            content: $this->message->content,
            userId: $this->message->sender_id
        );

        $isFlagged = $moderateContentAction->execute($dto);

        if ($isFlagged) {
            $this->message->update(['is_flagged' => true]);
            
            // Note: We could dispatch another event here to alert admins
            // or automatically hide the message on the frontend.
        }
    }
}

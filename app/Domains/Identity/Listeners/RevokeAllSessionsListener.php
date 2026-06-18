<?php

declare(strict_types=1);

namespace App\Domains\Identity\Listeners;

use App\Domains\Identity\Actions\RevokeSessionsAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RevokeAllSessionsListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private RevokeSessionsAction $revokeSessionsAction
    ) {
    }

    // Normally listens to a custom PasswordChanged event
    public function handle(object $event): void
    {
        // Immediate session invalidation
        $this->revokeSessionsAction->execute($event->user);
    }
}

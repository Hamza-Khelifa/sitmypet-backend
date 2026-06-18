<?php

declare(strict_types=1);

namespace App\Domains\Identity\Listeners;

use App\Domains\Identity\Actions\GenerateOTPAction;
use App\Domains\Identity\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Support\Facades\Mail;

class SendOTPVerificationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private GenerateOTPAction $generateOTPAction
    ) {
    }

    public function handle(UserRegistered $event): void
    {
        $otp = $this->generateOTPAction->execute($event->user);

        // Send Email
        // Mail::to($event->user->email)->send(new OTPVerificationMailable($otp->code));
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Identity\Jobs;

use App\Domains\Identity\Entities\User;
use App\Domains\Identity\Enums\UserStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessGDPRDeletionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly User $user
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            // 1. Revoke all active sessions and tokens
            $this->user->tokens()->delete(); // Sanctum
            $this->user->refreshTokens()->update(['revoked_at' => now()]);
            $this->user->activeSessions()->delete();
            $this->user->devices()->delete();

            // 2. Anonymize PII Data
            // Financial records (Escrow/Mangopay) tied to user_id remain intact per PSD2.
            $anonymizedEmail = 'deleted_' . Str::random(16) . '@anonymized.local';
            
            $this->user->update([
                'email' => $anonymizedEmail,
                'password' => Str::random(60),
                'status' => UserStatus::DELETED,
            ]);

            // 3. Optional: Trigger events to other Domains to clean up their PII
            // e.g., PII in Chat Messages, Profile Name, etc.
            
            // 4. Soft Delete the user record
            $this->user->delete();
        });
    }
}

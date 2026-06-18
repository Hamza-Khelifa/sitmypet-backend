<?php

declare(strict_types=1);

namespace App\Domains\Identity\Jobs;

use App\Domains\Identity\Entities\User;
use App\Domains\Infrastructure\Storage\StorageServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportUserDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly User $user
    ) {
    }

    public function handle(StorageServiceInterface $storage): void
    {
        $exportData = [
            'profile' => [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'status' => $this->user->status->value,
                'joined_at' => $this->user->created_at->toIso8601String(),
            ],
            'sessions' => $this->user->activeSessions->toArray(),
            'devices' => $this->user->devices->toArray(),
            'roles' => $this->user->roles->pluck('name')->toArray(),
        ];

        $jsonOutput = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $filename = 'gdpr_exports/' . $this->user->id . '_' . time() . '.json';
        
        $storage->put($filename, $jsonOutput, true);
        $downloadUrl = $storage->temporaryUrl($filename, now()->addHours(24));
        
        // Dispatch Email with URL
    }
}

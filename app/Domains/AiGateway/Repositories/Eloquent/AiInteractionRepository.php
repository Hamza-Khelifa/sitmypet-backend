<?php

declare(strict_types=1);

namespace App\Domains\AiGateway\Repositories\Eloquent;

use App\Domains\AiGateway\Entities\AiInteraction;
use App\Domains\AiGateway\Repositories\Contracts\AiInteractionRepositoryInterface;
use Illuminate\Support\Collection;

final class AiInteractionRepository implements AiInteractionRepositoryInterface
{
    public function getHistoryForUser(string $userId, int $limit = 20): Collection
    {
        return AiInteraction::where('user_id', $userId)
            ->where('type', 'assistant')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    public function getFlaggedInteractions(int $limit = 50): Collection
    {
        return AiInteraction::where('is_flagged', true)
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }
}

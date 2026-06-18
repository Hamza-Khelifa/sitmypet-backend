<?php

declare(strict_types=1);

namespace App\Domains\AiGateway\Repositories\Contracts;

use App\Domains\AiGateway\Entities\AiInteraction;
use Illuminate\Support\Collection;

interface AiInteractionRepositoryInterface
{
    public function getHistoryForUser(string $userId, int $limit = 20): Collection;
    public function getFlaggedInteractions(int $limit = 50): Collection;
}

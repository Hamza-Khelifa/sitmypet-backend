<?php

declare(strict_types=1);

namespace App\Domains\Communication\Repositories\Contracts;

use App\Domains\Communication\Entities\Conversation;
use Illuminate\Support\Collection;

interface ConversationRepositoryInterface
{
    public function findById(string $id): ?Conversation;
    public function findByUserId(string $userId): Collection;
    public function save(Conversation $conversation): Conversation;
}

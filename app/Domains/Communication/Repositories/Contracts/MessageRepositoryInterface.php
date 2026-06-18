<?php

declare(strict_types=1);

namespace App\Domains\Communication\Repositories\Contracts;

use App\Domains\Communication\Entities\Message;
use Illuminate\Support\Collection;

interface MessageRepositoryInterface
{
    public function findById(string $id): ?Message;
    public function findByConversationId(string $conversationId, int $limit = 50, int $offset = 0): Collection;
    public function save(Message $message): Message;
}

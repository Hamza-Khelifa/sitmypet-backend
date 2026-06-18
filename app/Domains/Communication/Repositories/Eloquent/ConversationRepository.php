<?php

declare(strict_types=1);

namespace App\Domains\Communication\Repositories\Eloquent;

use App\Domains\Communication\Entities\Conversation;
use App\Domains\Communication\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Support\Collection;

final class ConversationRepository implements ConversationRepositoryInterface
{
    public function findById(string $id): ?Conversation
    {
        return Conversation::with('participants')->find($id);
    }

    public function findByUserId(string $userId): Collection
    {
        return Conversation::whereHas('participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->orderByDesc('last_message_at')
        ->with(['participants', 'messages' => function ($query) {
            $query->latest()->limit(1); // Load latest message for list view
        }])
        ->get();
    }

    public function save(Conversation $conversation): Conversation
    {
        $conversation->save();
        return $conversation;
    }
}

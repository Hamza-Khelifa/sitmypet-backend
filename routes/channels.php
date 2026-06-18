<?php

use App\Domains\Communication\Entities\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{id}', function ($user, $id) {
    // Only users who are part of the conversation can listen to it
    $isParticipant = Conversation::where('id', $id)
        ->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();

    if ($isParticipant) {
        return ['id' => $user->id, 'name' => $user->name ?? 'User'];
    }

    return false;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});

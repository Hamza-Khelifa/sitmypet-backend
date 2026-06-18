<?php

declare(strict_types=1);

namespace App\Domains\Communication\Entities;

use App\Domains\Communication\Enums\MessageType;
use App\Domains\Identity\Entities\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id', // Nullable for system messages
        'type',
        'content', // Nullable if image-only
    ];

    protected function casts(): array
    {
        return [
            'type' => MessageType::class,
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}

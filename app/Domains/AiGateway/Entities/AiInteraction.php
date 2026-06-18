<?php

declare(strict_types=1);

namespace App\Domains\AiGateway\Entities;

use App\Domains\Identity\Entities\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInteraction extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'user_id',
        'type', // 'assistant', 'moderation'
        'prompt',
        'response',
        'is_flagged',
    ];

    protected function casts(): array
    {
        return [
            'is_flagged' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

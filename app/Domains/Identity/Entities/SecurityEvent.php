<?php

declare(strict_types=1);

namespace App\Domains\Identity\Entities;

use App\Domains\Identity\Enums\SecurityEventSeverity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityEvent extends Model
{
    use HasUuids;

    public const UPDATED_AT = null; // Immutable

    protected $fillable = [
        'user_id',
        'event_type',
        'severity',
        'context',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'severity' => SecurityEventSeverity::class,
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the user associated with this event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

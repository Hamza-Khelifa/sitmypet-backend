<?php

declare(strict_types=1);

namespace App\Domains\Identity\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefreshToken extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'token_hash',
        'token_family_id',
        'parent_token_id',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if the token is revoked.
     */
    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    /**
     * Determine if the token has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Determine if the token is valid for use.
     */
    public function isValid(): bool
    {
        return !$this->isRevoked() && !$this->isExpired();
    }
}

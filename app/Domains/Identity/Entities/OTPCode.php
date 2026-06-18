<?php

declare(strict_types=1);

namespace App\Domains\Identity\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OTPCode extends Model
{
    use HasUuids;

    protected $table = 'otp_codes';

    public const UPDATED_AT = null; // Immutable mostly, verified_at and attempts updated

    protected $fillable = [
        'user_id',
        'code',
        'attempts',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

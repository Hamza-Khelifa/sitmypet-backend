<?php

declare(strict_types=1);

namespace App\Domains\Payments\Entities;

use App\Domains\Identity\Entities\User;
use App\Domains\Payments\Enums\KycStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'user_id',
        'mangopay_user_id',
        'mangopay_wallet_id',
        'mangopay_bank_account_id',
        'kyc_status',
        'currency',
        'balance', // Cached balance from provider
    ];

    protected function casts(): array
    {
        return [
            'kyc_status' => KycStatus::class,
            'balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }
}

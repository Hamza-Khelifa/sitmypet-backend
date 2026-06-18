<?php

declare(strict_types=1);

namespace App\Domains\Payments\Entities;

use App\Domains\Payments\Enums\TransactionStatus;
use App\Domains\Payments\Enums\TransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'wallet_id',
        'mangopay_transaction_id',
        'type',
        'status',
        'amount',
        'fees',
        'currency',
        'reference_id', // Could be booking_id
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'amount' => 'decimal:2',
            'fees' => 'decimal:2',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}

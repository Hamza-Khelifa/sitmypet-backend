<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Entities;

use App\Domains\Marketplace\Enums\BidStatus;
use App\Domains\Profile\Entities\SitterProfile;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Marketplace\Traits\HasStateMachine;

class Bid extends Model
{
    use HasUuids, HasFactory, HasStateMachine;

    protected $fillable = [
        'demand_id',
        'sitter_id',
        'proposed_rate',
        'cover_letter',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'proposed_rate' => 'decimal:2',
            'status' => BidStatus::class,
        ];
    }

    public function demand(): BelongsTo
    {
        return $this->belongsTo(Demand::class);
    }

    public function sitter(): BelongsTo
    {
        return $this->belongsTo(SitterProfile::class, 'sitter_id');
    }

    protected function getAllowedTransitions(): array
    {
        return [
            BidStatus::PENDING->value => [BidStatus::ACCEPTED->value, BidStatus::REJECTED->value],
            BidStatus::ACCEPTED->value => [],
            BidStatus::REJECTED->value => [],
        ];
    }
}

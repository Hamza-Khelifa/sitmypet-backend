<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Entities;

use App\Domains\Marketplace\Enums\BookingStatus;
use App\Domains\Profile\Entities\SitterProfile;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Marketplace\Traits\HasStateMachine;

class Booking extends Model
{
    use HasUuids, HasFactory, HasStateMachine;

    protected $fillable = [
        'demand_id',
        'sitter_id',
        'status',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'total_price' => 'decimal:2',
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
            BookingStatus::CONFIRMED->value => [BookingStatus::IN_PROGRESS->value, BookingStatus::CANCELLED->value],
            BookingStatus::IN_PROGRESS->value => [BookingStatus::COMPLETED->value, BookingStatus::CANCELLED->value],
            BookingStatus::COMPLETED->value => [],
            BookingStatus::CANCELLED->value => [],
        ];
    }
}

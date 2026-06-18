<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Entities;

use App\Domains\Identity\Entities\User;
use App\Domains\Marketplace\Enums\DemandStatus;
use App\Domains\Pets\Entities\Pet;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Marketplace\Traits\HasStateMachine;

class Demand extends Model
{
    use HasUuids, HasFactory, HasStateMachine;

    protected $fillable = [
        'owner_id',
        'start_date',
        'end_date',
        'status',
        'requirements',
        // Note: location is handled via raw DB updates for PostGIS
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'status' => DemandStatus::class,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function pets(): BelongsToMany
    {
        // Using a pivot table 'demand_pet'
        return $this->belongsToMany(Pet::class, 'demand_pet');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function booking(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Booking::class);
    }

    protected function getAllowedTransitions(): array
    {
        return [
            DemandStatus::OPEN->value => [DemandStatus::ASSIGNED->value, DemandStatus::CANCELLED->value],
            DemandStatus::ASSIGNED->value => [DemandStatus::COMPLETED->value, DemandStatus::CANCELLED->value, DemandStatus::OPEN->value],
            // Once completed or cancelled, it cannot change
            DemandStatus::COMPLETED->value => [],
            DemandStatus::CANCELLED->value => [],
        ];
    }
}

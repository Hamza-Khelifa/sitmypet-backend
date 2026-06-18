<?php

declare(strict_types=1);

namespace App\Domains\Reviews\Entities;

use App\Domains\Identity\Entities\User;
use App\Domains\Marketplace\Entities\Booking;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'booking_id',
        'reviewer_id',
        'reviewee_id',
        'rating',
        'content',
        'is_flagged',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_flagged' => 'boolean',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }
}

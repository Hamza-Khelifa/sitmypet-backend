<?php

declare(strict_types=1);

namespace App\Domains\Profile\Entities;

use App\Domains\Profile\Enums\CertificationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certification extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'sitter_profile_id',
        'type',
        'document_url',
        'status',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CertificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Get the sitter profile that owns the certification.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(SitterProfile::class, 'sitter_profile_id');
    }
}

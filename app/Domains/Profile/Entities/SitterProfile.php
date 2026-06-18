<?php

declare(strict_types=1);

namespace App\Domains\Profile\Entities;

use App\Domains\Identity\Entities\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SitterProfile extends Model implements HasMedia
{
    use HasUuids, HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'bio',
        'hourly_rate',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the certifications for the profile.
     */
    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class, 'sitter_profile_id');
    }

    /**
     * Define media collections and conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('avatar')
            ->width(300)
            ->height(300)
            ->format('webp')
            ->nonQueued(); // or keep queued if using jobs
    }
}

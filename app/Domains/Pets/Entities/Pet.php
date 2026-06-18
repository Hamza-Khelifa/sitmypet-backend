<?php

declare(strict_types=1);

namespace App\Domains\Pets\Entities;

use App\Domains\Identity\Entities\User;
use App\Domains\Pets\Enums\SpeciesType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'species',
        'breed',
        'birth_date',
        'weight',
        'behavior_notes',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'species' => SpeciesType::class,
            'birth_date' => 'date',
            'weight' => 'decimal:2',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PetPhoto::class);
    }
}

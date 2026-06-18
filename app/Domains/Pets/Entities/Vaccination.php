<?php

declare(strict_types=1);

namespace App\Domains\Pets\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaccination extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'pet_id',
        'name',
        'date_administered',
        'next_due_date',
        'document_url',
    ];

    protected function casts(): array
    {
        return [
            'date_administered' => 'date',
            'next_due_date' => 'date',
        ];
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
}

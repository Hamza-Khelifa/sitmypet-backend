<?php

declare(strict_types=1);

namespace App\Domains\Communication\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'message_id',
        'file_path',
        'file_type',
        'file_size',
        'original_name',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}

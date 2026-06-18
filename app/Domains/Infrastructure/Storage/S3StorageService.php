<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Storage;

use Illuminate\Support\Facades\Storage;

class S3StorageService implements StorageServiceInterface
{
    public function put(string $path, string $contents, bool $isPrivate = true): bool
    {
        $disk = $isPrivate ? 's3-private' : 's3-public';
        return Storage::disk($disk)->put($path, $contents);
    }

    public function temporaryUrl(string $path, \DateTimeInterface $expiration): string
    {
        return Storage::disk('s3-private')->temporaryUrl($path, $expiration);
    }
}

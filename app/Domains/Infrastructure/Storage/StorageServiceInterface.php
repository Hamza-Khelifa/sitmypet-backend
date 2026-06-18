<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Storage;

interface StorageServiceInterface
{
    /**
     * Store data securely.
     */
    public function put(string $path, string $contents, bool $isPrivate = true): bool;

    /**
     * Generate a temporary signed URL for download.
     */
    public function temporaryUrl(string $path, \DateTimeInterface $expiration): string;
}

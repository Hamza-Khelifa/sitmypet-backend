<?php

declare(strict_types=1);

namespace App\Domains\Identity\Repositories\Contracts;

interface SocialAccountRepositoryInterface
{
    public function findByProvider(string $provider, string $providerUserId): ?object;

    public function create(array $data): object;
}

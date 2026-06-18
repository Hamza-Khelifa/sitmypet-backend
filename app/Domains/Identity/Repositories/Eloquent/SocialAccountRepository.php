<?php

declare(strict_types=1);

namespace App\Domains\Identity\Repositories\Eloquent;

use App\Domains\Identity\Entities\SocialAccount;
use App\Domains\Identity\Repositories\Contracts\SocialAccountRepositoryInterface;

class SocialAccountRepository implements SocialAccountRepositoryInterface
{
    public function findByProvider(string $provider, string $providerUserId): ?object
    {
        return SocialAccount::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();
    }

    public function create(array $data): object
    {
        return SocialAccount::create($data);
    }
}

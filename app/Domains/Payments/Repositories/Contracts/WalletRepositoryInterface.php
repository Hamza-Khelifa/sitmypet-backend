<?php

declare(strict_types=1);

namespace App\Domains\Payments\Repositories\Contracts;

use App\Domains\Payments\Entities\Wallet;

interface WalletRepositoryInterface
{
    public function findById(string $id): ?Wallet;
    public function findByUserId(string $userId): ?Wallet;
    public function save(Wallet $wallet): Wallet;
}

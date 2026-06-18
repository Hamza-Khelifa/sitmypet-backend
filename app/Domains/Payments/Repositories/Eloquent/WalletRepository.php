<?php

declare(strict_types=1);

namespace App\Domains\Payments\Repositories\Eloquent;

use App\Domains\Payments\Entities\Wallet;
use App\Domains\Payments\Repositories\Contracts\WalletRepositoryInterface;

final class WalletRepository implements WalletRepositoryInterface
{
    public function findById(string $id): ?Wallet
    {
        return Wallet::find($id);
    }

    public function findByUserId(string $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }

    public function save(Wallet $wallet): Wallet
    {
        $wallet->save();
        return $wallet;
    }
}

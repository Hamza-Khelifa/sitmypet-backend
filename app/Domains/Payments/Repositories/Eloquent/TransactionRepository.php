<?php

declare(strict_types=1);

namespace App\Domains\Payments\Repositories\Eloquent;

use App\Domains\Payments\Entities\Transaction;
use App\Domains\Payments\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Support\Collection;

final class TransactionRepository implements TransactionRepositoryInterface
{
    public function findById(string $id): ?Transaction
    {
        return Transaction::find($id);
    }

    public function findByWalletId(string $walletId): Collection
    {
        return Transaction::where('wallet_id', $walletId)->orderBy('created_at', 'desc')->get();
    }

    public function save(Transaction $transaction): Transaction
    {
        $transaction->save();
        return $transaction;
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Payments\Repositories\Contracts;

use App\Domains\Payments\Entities\Transaction;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    public function findById(string $id): ?Transaction;
    public function findByWalletId(string $walletId): Collection;
    public function save(Transaction $transaction): Transaction;
}

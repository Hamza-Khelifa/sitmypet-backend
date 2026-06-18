<?php

declare(strict_types=1);

namespace App\Domains\Payments\Actions;

use App\Domains\Payments\DTOs\ReleaseEscrowDTO;
use App\Domains\Payments\Entities\Transaction;
use App\Domains\Payments\Enums\TransactionStatus;
use App\Domains\Payments\Enums\TransactionType;
use App\Domains\Payments\Events\TransactionStatusUpdated;
use App\Domains\Payments\Integrations\Contracts\MangopayGatewayInterface;
use App\Domains\Payments\Repositories\Contracts\TransactionRepositoryInterface;
use App\Domains\Payments\Repositories\Contracts\WalletRepositoryInterface;
use App\Domains\Payments\ValueObjects\Money;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;

final class ReleaseEscrowAction
{
    public function __construct(
        private readonly MangopayGatewayInterface $gateway,
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly TransactionRepositoryInterface $transactionRepository
    ) {}

    public function execute(ReleaseEscrowDTO $dto): Transaction
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($dto) {
            $debitedWallet = \App\Domains\Payments\Entities\Wallet::where('id', $dto->debitedWalletId)->lockForUpdate()->first();
            $creditedWallet = \App\Domains\Payments\Entities\Wallet::where('id', $dto->creditedWalletId)->lockForUpdate()->first();

            if (!$debitedWallet || !$creditedWallet) {
                throw new InvalidArgumentException('Wallet not found');
            }

            if ($debitedWallet->balance < $dto->amount) {
                throw new \RuntimeException('Insufficient funds in escrow wallet');
            }

            $money = Money::fromDecimal($dto->amount, $dto->currency);
            $fees = Money::fromDecimal($dto->platformFee, $dto->currency);
            $idempotencyKey = hash('sha256', $debitedWallet->id . '_' . $creditedWallet->id . '_release_' . $dto->amount . '_' . microtime());

            $mangopayTxId = $this->gateway->createTransfer(
                $debitedWallet->mangopay_user_id,
                $debitedWallet->mangopay_wallet_id,
                $creditedWallet->mangopay_wallet_id,
                $money->amountInCents,
                $fees->amountInCents,
                $dto->currency,
                $idempotencyKey
            );

            $transaction = new Transaction([
                'wallet_id' => $creditedWallet->id,
                'mangopay_transaction_id' => $mangopayTxId,
                'type' => TransactionType::TRANSFER->value,
                'status' => TransactionStatus::SUCCEEDED->value,
                'amount' => $dto->amount,
                'fees' => $dto->platformFee,
                'currency' => $dto->currency,
            ]);

            $this->transactionRepository->save($transaction);

            // Update local balance
            $debitedWallet->balance -= $dto->amount;
            $creditedWallet->balance += ($dto->amount - $dto->platformFee);

            $this->walletRepository->save($debitedWallet);
            $this->walletRepository->save($creditedWallet);

            Event::dispatch(new TransactionStatusUpdated($transaction->id));

            return $transaction;
        });
    }
}

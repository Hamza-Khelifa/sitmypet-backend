<?php

declare(strict_types=1);

namespace App\Domains\Payments\Actions;

use App\Domains\Payments\DTOs\ProcessPayOutDTO;
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

final class ProcessPayOutAction
{
    public function __construct(
        private readonly MangopayGatewayInterface $gateway,
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly TransactionRepositoryInterface $transactionRepository
    ) {}

    public function execute(ProcessPayOutDTO $dto): Transaction
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($dto) {
            $wallet = \App\Domains\Payments\Entities\Wallet::where('id', $dto->walletId)->lockForUpdate()->first();

            if (!$wallet || !$wallet->mangopay_bank_account_id) {
                throw new InvalidArgumentException('Wallet or Bank Account not found');
            }

            if ($wallet->balance < $dto->amount) {
                throw new \RuntimeException('Insufficient funds for payout');
            }

            $money = Money::fromDecimal($dto->amount, $dto->currency);
            $idempotencyKey = hash('sha256', 'payout_' . $wallet->id . '_' . $dto->amount . '_' . microtime());

            $mangopayTxId = $this->gateway->createPayOut(
                $wallet->mangopay_user_id,
                $wallet->mangopay_wallet_id,
                $wallet->mangopay_bank_account_id,
                $money->amountInCents,
                $dto->currency,
                $idempotencyKey
            );

            $transaction = new Transaction([
                'wallet_id' => $wallet->id,
                'mangopay_transaction_id' => $mangopayTxId,
                'type' => TransactionType::PAY_OUT->value,
                'status' => TransactionStatus::PENDING->value,
                'amount' => $dto->amount,
                'fees' => 0.00,
                'currency' => $dto->currency,
            ]);

            $this->transactionRepository->save($transaction);

            // Deduct from local balance
            $wallet->balance -= $dto->amount;
            $this->walletRepository->save($wallet);

            Event::dispatch(new TransactionStatusUpdated($transaction->id));

            return $transaction;
        });
    }
}

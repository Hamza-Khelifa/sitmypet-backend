<?php

declare(strict_types=1);

namespace App\Domains\Payments\Actions;

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

final class RefundEscrowAction
{
    public function __construct(
        private readonly MangopayGatewayInterface $gateway,
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly TransactionRepositoryInterface $transactionRepository
    ) {}

    public function execute(string $transactionId): Transaction
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($transactionId) {
            $payInTransaction = \App\Domains\Payments\Entities\Transaction::where('id', $transactionId)->lockForUpdate()->first();

            if (!$payInTransaction || $payInTransaction->type !== TransactionType::PAY_IN) {
                throw new InvalidArgumentException('Invalid PayIn transaction');
            }

            $wallet = \App\Domains\Payments\Entities\Wallet::where('id', $payInTransaction->wallet_id)->lockForUpdate()->first();

            if ($wallet->balance < $payInTransaction->amount) {
                throw new \RuntimeException('Insufficient funds to refund');
            }

            $money = Money::fromDecimal((float) $payInTransaction->amount, $payInTransaction->currency);
            $idempotencyKey = hash('sha256', 'refund_' . $payInTransaction->id);

            $mangopayTxId = $this->gateway->refundPayIn(
                $wallet->mangopay_user_id,
                $payInTransaction->mangopay_transaction_id,
                $money->amountInCents,
                $payInTransaction->currency,
                $idempotencyKey
            );

            $refundTransaction = new Transaction([
                'wallet_id' => $wallet->id,
                'mangopay_transaction_id' => $mangopayTxId,
                'type' => TransactionType::REFUND->value,
                'status' => TransactionStatus::SUCCEEDED->value,
                'amount' => $payInTransaction->amount,
                'fees' => 0.00,
                'currency' => $payInTransaction->currency,
                'reference_id' => $payInTransaction->id,
            ]);

            $this->transactionRepository->save($refundTransaction);

            // Update local balance
            $wallet->balance -= $payInTransaction->amount;
            $this->walletRepository->save($wallet);

            Event::dispatch(new TransactionStatusUpdated($refundTransaction->id));

            return $refundTransaction;
        });
    }
}

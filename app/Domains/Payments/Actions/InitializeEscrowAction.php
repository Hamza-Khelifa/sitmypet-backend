<?php

declare(strict_types=1);

namespace App\Domains\Payments\Actions;

use App\Domains\Payments\DTOs\InitializeEscrowDTO;
use App\Domains\Payments\Entities\Transaction;
use App\Domains\Payments\Enums\TransactionStatus;
use App\Domains\Payments\Enums\TransactionType;
use App\Domains\Payments\Integrations\Contracts\MangopayGatewayInterface;
use App\Domains\Payments\Repositories\Contracts\TransactionRepositoryInterface;
use App\Domains\Payments\Repositories\Contracts\WalletRepositoryInterface;
use App\Domains\Payments\ValueObjects\Money;
use InvalidArgumentException;

final class InitializeEscrowAction
{
    public function __construct(
        private readonly MangopayGatewayInterface $gateway,
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly TransactionRepositoryInterface $transactionRepository
    ) {}

    public function execute(InitializeEscrowDTO $dto): array
    {
        $wallet = $this->walletRepository->findById($dto->walletId);

        if (!$wallet) {
            throw new InvalidArgumentException('Wallet not found');
        }

        $money = Money::fromDecimal($dto->amount, $dto->currency);
        $idempotencyKey = hash('sha256', $wallet->id . '_payin_' . $dto->amount . '_' . microtime());

        $response = $this->gateway->createPayIn(
            $wallet->mangopay_user_id,
            $wallet->mangopay_wallet_id,
            $money->amountInCents,
            $dto->currency,
            $dto->returnUrl,
            $idempotencyKey
        );

        $transaction = new Transaction([
            'wallet_id' => $wallet->id,
            'mangopay_transaction_id' => $response['TransactionId'],
            'type' => TransactionType::PAY_IN->value,
            'status' => TransactionStatus::PENDING->value,
            'amount' => $dto->amount,
            'fees' => 0.00,
            'currency' => $dto->currency,
        ]);

        $this->transactionRepository->save($transaction);

        return [
            'transaction_id' => $transaction->id,
            'redirect_url' => $response['RedirectUrl'],
        ];
    }
}

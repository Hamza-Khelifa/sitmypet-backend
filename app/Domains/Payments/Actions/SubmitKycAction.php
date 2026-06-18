<?php

declare(strict_types=1);

namespace App\Domains\Payments\Actions;

use App\Domains\Payments\Enums\KycStatus;
use App\Domains\Payments\Integrations\Contracts\MangopayGatewayInterface;
use App\Domains\Payments\Repositories\Contracts\WalletRepositoryInterface;
use InvalidArgumentException;

final class SubmitKycAction
{
    public function __construct(
        private readonly MangopayGatewayInterface $gateway,
        private readonly WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(string $walletId, string $documentBase64): bool
    {
        $wallet = $this->walletRepository->findById($walletId);

        if (!$wallet) {
            throw new InvalidArgumentException('Wallet not found');
        }

        $this->gateway->submitKycDocument($wallet->mangopay_user_id, $documentBase64);

        $wallet->kyc_status = KycStatus::VALIDATION_ASKED->value;
        $this->walletRepository->save($wallet);

        return true;
    }
}

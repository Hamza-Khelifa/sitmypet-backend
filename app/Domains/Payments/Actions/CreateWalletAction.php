<?php

declare(strict_types=1);

namespace App\Domains\Payments\Actions;

use App\Domains\Payments\DTOs\CreateWalletDTO;
use App\Domains\Payments\Entities\Wallet;
use App\Domains\Payments\Enums\KycStatus;
use App\Domains\Payments\Events\WalletCreated;
use App\Domains\Payments\Integrations\Contracts\MangopayGatewayInterface;
use App\Domains\Payments\Repositories\Contracts\WalletRepositoryInterface;
use Illuminate\Support\Facades\Event;

final class CreateWalletAction
{
    public function __construct(
        private readonly MangopayGatewayInterface $gateway,
        private readonly WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(CreateWalletDTO $dto): Wallet
    {
        // Create user in Mangopay
        $mangopayUserId = $this->gateway->createUser([
            'FirstName' => $dto->firstName,
            'LastName' => $dto->lastName,
            'Email' => $dto->email,
            'Nationality' => $dto->nationality,
            'CountryOfResidence' => $dto->countryOfResidence,
        ]);

        // Create wallet in Mangopay
        $mangopayWalletId = $this->gateway->createWallet(
            $mangopayUserId,
            $dto->currency,
            "Wallet for user {$dto->userId}"
        );

        $wallet = new Wallet([
            'user_id' => $dto->userId,
            'mangopay_user_id' => $mangopayUserId,
            'mangopay_wallet_id' => $mangopayWalletId,
            'kyc_status' => KycStatus::CREATED->value,
            'currency' => $dto->currency,
            'balance' => 0.00,
        ]);

        $wallet = $this->walletRepository->save($wallet);

        Event::dispatch(new WalletCreated($wallet->id));

        return $wallet;
    }
}

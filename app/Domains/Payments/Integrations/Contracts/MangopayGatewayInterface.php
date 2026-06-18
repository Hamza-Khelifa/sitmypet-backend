<?php

declare(strict_types=1);

namespace App\Domains\Payments\Integrations\Contracts;

interface MangopayGatewayInterface
{
    public function createUser(array $data): string;
    
    public function createWallet(string $mangopayUserId, string $currency, string $description): string;
    
    public function submitKycDocument(string $mangopayUserId, string $documentBase64, ?string $idempotencyKey = null): string;
    
    public function createPayIn(string $mangopayUserId, string $walletId, int $amount, string $currency, string $returnUrl, ?string $idempotencyKey = null): array;
    
    public function createTransfer(string $authorId, string $debitedWalletId, string $creditedWalletId, int $amount, int $fees, string $currency, ?string $idempotencyKey = null): string;
    
    public function createPayOut(string $authorId, string $walletId, string $bankAccountId, int $amount, string $currency, ?string $idempotencyKey = null): string;
    
    public function refundPayIn(string $authorId, string $payInId, int $amount, string $currency, ?string $idempotencyKey = null): string;

    public function getTransaction(string $transactionId): array;
}

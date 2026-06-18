<?php

declare(strict_types=1);

namespace App\Domains\Payments\Integrations;

use App\Domains\Payments\Integrations\Contracts\MangopayGatewayInterface;
use Illuminate\Support\Str;

final class MangopayService implements MangopayGatewayInterface
{
    private string $baseUrl;
    private string $clientId;
    private string $clientPassword;

    public function __construct()
    {
        $this->baseUrl = config('services.mangopay.base_url', 'https://api.sandbox.mangopay.com/v2.01/');
        $this->clientId = config('services.mangopay.client_id', 'demo');
        $this->clientPassword = config('services.mangopay.client_password', 'demo');
    }

    private function getClient(?string $idempotencyKey = null): \Illuminate\Http\Client\PendingRequest
    {
        $client = \Illuminate\Support\Facades\Http::withBasicAuth($this->clientId, $this->clientPassword)
            ->baseUrl($this->baseUrl . $this->clientId . '/');
            
        if ($idempotencyKey) {
            $client->withHeaders(['Idempotency-Key' => $idempotencyKey]);
        }
        
        return $client;
    }

    public function createUser(array $data): string
    {
        $response = $this->getClient()->post('users/natural', $data)->throw();
        return (string) $response->json('Id');
    }
    
    public function createWallet(string $mangopayUserId, string $currency, string $description): string
    {
        $response = $this->getClient()->post('wallets', [
            'Owners' => [$mangopayUserId],
            'Description' => $description,
            'Currency' => $currency,
        ])->throw();

        return (string) $response->json('Id');
    }
    
    public function submitKycDocument(string $mangopayUserId, string $documentBase64, ?string $idempotencyKey = null): string
    {
        // 1. Create document
        $docResponse = $this->getClient($idempotencyKey)->post("users/{$mangopayUserId}/kyc/documents", [
            'Type' => 'IDENTITY_PROOF',
        ])->throw();
        
        $docId = $docResponse->json('Id');

        // 2. Upload page
        $this->getClient()->post("users/{$mangopayUserId}/kyc/documents/{$docId}/pages", [
            'File' => $documentBase64,
        ])->throw();

        // 3. Submit
        $this->getClient()->put("users/{$mangopayUserId}/kyc/documents/{$docId}", [
            'Status' => 'VALIDATION_ASKED',
        ])->throw();

        return (string) $docId;
    }
    
    public function createPayIn(string $mangopayUserId, string $walletId, int $amount, string $currency, string $returnUrl, ?string $idempotencyKey = null): array
    {
        $response = $this->getClient($idempotencyKey)->post('payins/card/web', [
            'AuthorId' => $mangopayUserId,
            'CreditedWalletId' => $walletId,
            'DebitedFunds' => ['Currency' => $currency, 'Amount' => $amount],
            'Fees' => ['Currency' => $currency, 'Amount' => 0],
            'ReturnURL' => $returnUrl,
            'CardType' => 'CB_VISA_MASTERCARD',
        ])->throw();

        return [
            'TransactionId' => (string) $response->json('Id'),
            'RedirectUrl' => $response->json('ExecutionDetails.RedirectURL') ?? $response->json('RedirectURL')
        ];
    }
    
    public function createTransfer(string $authorId, string $debitedWalletId, string $creditedWalletId, int $amount, int $fees, string $currency, ?string $idempotencyKey = null): string
    {
        $response = $this->getClient($idempotencyKey)->post('transfers', [
            'AuthorId' => $authorId,
            'DebitedFunds' => ['Currency' => $currency, 'Amount' => $amount],
            'Fees' => ['Currency' => $currency, 'Amount' => $fees],
            'DebitedWalletId' => $debitedWalletId,
            'CreditedWalletId' => $creditedWalletId,
        ])->throw();

        return (string) $response->json('Id');
    }
    
    public function createPayOut(string $authorId, string $walletId, string $bankAccountId, int $amount, string $currency, ?string $idempotencyKey = null): string
    {
        $response = $this->getClient($idempotencyKey)->post('payouts/bankwire', [
            'AuthorId' => $authorId,
            'DebitedWalletId' => $walletId,
            'DebitedFunds' => ['Currency' => $currency, 'Amount' => $amount],
            'Fees' => ['Currency' => $currency, 'Amount' => 0],
            'BankAccountId' => $bankAccountId,
            'BankWireRef' => 'Payout',
        ])->throw();

        return (string) $response->json('Id');
    }
    
    public function refundPayIn(string $authorId, string $payInId, int $amount, string $currency, ?string $idempotencyKey = null): string
    {
        $response = $this->getClient($idempotencyKey)->post("payins/{$payInId}/refunds", [
            'AuthorId' => $authorId,
            'DebitedFunds' => ['Currency' => $currency, 'Amount' => $amount],
            'Fees' => ['Currency' => $currency, 'Amount' => 0],
        ])->throw();

        return (string) $response->json('Id');
    }

    public function getTransaction(string $transactionId): array
    {
        $response = $this->getClient()->get("transactions/{$transactionId}")->throw();
        return $response->json();
    }
}

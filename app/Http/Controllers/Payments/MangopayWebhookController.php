<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Domains\Payments\Enums\KycStatus;
use App\Domains\Payments\Enums\TransactionStatus;
use App\Domains\Payments\Events\TransactionStatusUpdated;
use App\Domains\Payments\Repositories\Contracts\TransactionRepositoryInterface;
use App\Domains\Payments\Repositories\Contracts\WalletRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class MangopayWebhookController extends Controller
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly \App\Domains\Payments\Integrations\Contracts\MangopayGatewayInterface $gateway
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $eventType = $request->query('EventType');
        $resourceId = $request->query('RessourceId'); // Mangopay spelling

        if (!$eventType || !$resourceId) {
            return response()->json(['message' => 'Invalid webhook payload'], 400);
        }

        switch ($eventType) {
            case 'PAYIN_NORMAL_SUCCEEDED':
            case 'TRANSFER_NORMAL_SUCCEEDED':
            case 'PAYOUT_NORMAL_SUCCEEDED':
                // Real Reconciliation: Fetch from Mangopay API to verify it is actually SUCCESSFUL
                try {
                    $remoteTx = $this->gateway->getTransaction($resourceId);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to verify Mangopay Transaction {$resourceId}: " . $e->getMessage());
                    return response()->json(['message' => 'Failed to verify transaction'], 500);
                }

                if (($remoteTx['Status'] ?? '') !== 'SUCCEEDED') {
                    \Illuminate\Support\Facades\Log::warning("Transaction {$resourceId} reported as SUCCEEDED in webhook but is actually " . ($remoteTx['Status'] ?? 'UNKNOWN'));
                    return response()->json(['message' => 'Transaction status mismatch'], 400);
                }

                \Illuminate\Support\Facades\DB::transaction(function () use ($resourceId, $remoteTx) {
                    $transaction = \App\Domains\Payments\Entities\Transaction::where('mangopay_transaction_id', $resourceId)
                        ->lockForUpdate()
                        ->first();
                        
                    if ($transaction && $transaction->status !== TransactionStatus::SUCCEEDED) {
                        $transaction->status = TransactionStatus::SUCCEEDED->value;
                        $this->transactionRepository->save($transaction);
                        
                        // We do not eagerly update balances here for Transfers/Payouts since the Action might have already deducted/added.
                        // Wait, if it's a PAYIN, the user added money.
                        if ($transaction->type === \App\Domains\Payments\Enums\TransactionType::PAY_IN) {
                            $wallet = $this->walletRepository->findById($transaction->wallet_id);
                            // Lock wallet
                            $wallet = \App\Domains\Payments\Entities\Wallet::where('id', $wallet->id)->lockForUpdate()->first();
                            $wallet->balance += $transaction->amount;
                            $this->walletRepository->save($wallet);
                        }

                        Event::dispatch(new TransactionStatusUpdated($transaction->id));
                    }
                });
                break;

            case 'KYC_SUCCEEDED':
                // Note: Simplified for scaffolding:
                $wallet = \App\Domains\Payments\Entities\Wallet::where('mangopay_user_id', $resourceId)->first();
                if ($wallet) {
                    $wallet->kyc_status = KycStatus::VALIDATED->value;
                    $this->walletRepository->save($wallet);
                }
                break;
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}

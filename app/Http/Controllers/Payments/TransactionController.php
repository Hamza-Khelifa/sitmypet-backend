<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Domains\Payments\Actions\InitializeEscrowAction;
use App\Domains\Payments\DTOs\InitializeEscrowDTO;
use App\Domains\Payments\Repositories\Contracts\TransactionRepositoryInterface;
use App\Domains\Payments\Repositories\Contracts\WalletRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly InitializeEscrowAction $initializeEscrowAction
    ) {}

    public function index(string $walletId): JsonResponse
    {
        $transactions = $this->transactionRepository->findByWalletId($walletId);
        return response()->json(['data' => $transactions]);
    }

    public function payIn(Request $request, string $walletId): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'return_url' => ['required', 'url'],
        ]);

        $dto = new InitializeEscrowDTO(
            walletId: $walletId,
            amount: (float) $validated['amount'],
            returnUrl: $validated['return_url']
        );

        $result = $this->initializeEscrowAction->execute($dto);

        return response()->json([
            'message' => 'PayIn initialized.',
            'data' => $result
        ], 201);
    }
}

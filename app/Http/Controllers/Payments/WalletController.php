<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Domains\Payments\Actions\CreateWalletAction;
use App\Domains\Payments\Actions\SubmitKycAction;
use App\Domains\Payments\DTOs\CreateWalletDTO;
use App\Domains\Payments\Repositories\Contracts\WalletRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly CreateWalletAction $createWalletAction,
        private readonly SubmitKycAction $submitKycAction
    ) {}

    public function show(Request $request): JsonResponse
    {
        $wallet = $this->walletRepository->findByUserId($request->user()->id);
        
        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        return response()->json(['data' => $wallet]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'nationality' => ['required', 'string', 'size:2'],
            'country_of_residence' => ['required', 'string', 'size:2'],
        ]);

        $dto = new CreateWalletDTO(
            userId: $request->user()->id,
            firstName: $validated['first_name'],
            lastName: $validated['last_name'],
            email: $request->user()->email,
            nationality: $validated['nationality'],
            countryOfResidence: $validated['country_of_residence']
        );

        $wallet = $this->createWalletAction->execute($dto);

        return response()->json([
            'message' => 'Wallet created successfully.',
            'data' => $wallet
        ], 201);
    }

    public function submitKyc(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'document_base64' => ['required', 'string'],
        ]);

        $this->submitKycAction->execute($id, $validated['document_base64']);

        return response()->json(['message' => 'KYC document submitted for validation.']);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketplace;

use App\Domains\Marketplace\Actions\AcceptBidAction;
use App\Domains\Marketplace\Actions\SubmitBidAction;
use App\Domains\Marketplace\DTOs\SubmitBidDTO;
use App\Domains\Marketplace\Repositories\Contracts\BidRepositoryInterface;
use App\Domains\Marketplace\Repositories\Contracts\DemandRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\SubmitBidRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BidController extends Controller
{
    public function __construct(
        private readonly SubmitBidAction $submitBidAction,
        private readonly AcceptBidAction $acceptBidAction,
        private readonly DemandRepositoryInterface $demandRepository,
        private readonly BidRepositoryInterface $bidRepository
    ) {}

    public function store(SubmitBidRequest $request, string $demandId): JsonResponse
    {
        $demand = $this->demandRepository->findById($demandId);
        if (!$demand) {
            return response()->json(['message' => 'Demand not found'], 404);
        }

        $dto = new SubmitBidDTO(
            demandId: $demandId,
            sitterId: $request->user()->id,
            proposedRate: (float) $request->validated('proposed_rate'),
            coverLetter: $request->validated('cover_letter')
        );

        $bid = $this->submitBidAction->execute($dto);

        return response()->json([
            'message' => 'Bid submitted successfully.',
            'data' => $bid
        ], 201);
    }

    public function index(string $demandId): JsonResponse
    {
        $bids = $this->bidRepository->findByDemandId($demandId);
        return response()->json(['data' => $bids]);
    }

    public function accept(Request $request, string $bidId): JsonResponse
    {
        $bid = $this->bidRepository->findById($bidId);
        if (!$bid) {
            return response()->json(['message' => 'Bid not found'], 404);
        }

        // Authorization should ideally happen here using Gate or Policy

        $booking = $this->acceptBidAction->execute($bidId);

        return response()->json([
            'message' => 'Bid accepted. Booking created.',
            'data' => $booking
        ]);
    }
}

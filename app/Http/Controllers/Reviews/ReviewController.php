<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Domains\Marketplace\Repositories\Contracts\BookingRepositoryInterface;
use App\Domains\Reviews\Actions\SubmitReviewAction;
use App\Domains\Reviews\DTOs\SubmitReviewDTO;
use App\Domains\Reviews\Repositories\Contracts\ReviewRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepository,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly SubmitReviewAction $submitReviewAction
    ) {}

    public function index(Request $request, string $userId): JsonResponse
    {
        $limit = (int) $request->query('limit', '10');
        $offset = (int) $request->query('offset', '0');

        $reviews = $this->reviewRepository->getReviewsForUser($userId, $limit, $offset);
        $averageRating = $this->reviewRepository->getAverageRatingForUser($userId);

        return response()->json([
            'data' => $reviews,
            'meta' => [
                'average_rating' => $averageRating
            ]
        ]);
    }

    public function store(Request $request, string $bookingId): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = $this->bookingRepository->findById($bookingId);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        // $this->authorize('create', [\App\Domains\Reviews\Entities\Review::class, $booking]);

        $dto = new SubmitReviewDTO(
            bookingId: $bookingId,
            reviewerId: $request->user()->id,
            rating: $validated['rating'],
            content: $validated['content'] ?? null
        );

        $review = $this->submitReviewAction->execute($dto);

        return response()->json([
            'message' => 'Review submitted successfully',
            'data' => $review
        ], 201);
    }
}

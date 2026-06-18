<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Reviews;

use App\Domains\Marketplace\Entities\Booking;
use App\Domains\Reviews\Entities\Review;
use App\Domains\Identity\Entities\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * List reviews for a specific user.
     */
    public function index(string $userId): JsonResponse
    {
        $reviews = Review::where('reviewee_id', $userId)
            ->where('is_flagged', false)
            ->with('reviewer:id,email')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($reviews);
    }

    /**
     * Store a new review for a completed booking.
     */
    public function store(Request $request, string $bookingId): JsonResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        // 1. Fetch booking and authorize
        $booking = Booking::findOrFail($bookingId);

        if ($booking->owner_id !== $user->id && $booking->sitter_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to review this booking.'], 403);
        }

        // 2. Ensure booking is completed
        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'Can only review completed bookings.'], 422);
        }

        // 3. Prevent duplicate reviews
        $existingReview = Review::where('booking_id', $bookingId)
            ->where('reviewer_id', $user->id)
            ->first();

        if ($existingReview) {
            return response()->json(['message' => 'You have already reviewed this booking.'], 409);
        }

        $revieweeId = ($booking->owner_id === $user->id) ? $booking->sitter_id : $booking->owner_id;

        DB::beginTransaction();
        try {
            // 4. Create review
            $review = Review::create([
                'booking_id' => $booking->id,
                'reviewer_id' => $user->id,
                'reviewee_id' => $revieweeId,
                'rating' => $request->input('rating'),
                'content' => $request->input('content'),
                'is_flagged' => false,
            ]);

            // 5. Update user aggregate rating dynamically
            $reviewee = User::findOrFail($revieweeId);
            $newCount = $reviewee->reviews_count + 1;
            $newAggregate = (($reviewee->aggregate_rating * $reviewee->reviews_count) + $request->rating) / $newCount;

            $reviewee->update([
                'reviews_count' => $newCount,
                'aggregate_rating' => round($newAggregate, 2),
            ]);

            DB::commit();

            return response()->json($review, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

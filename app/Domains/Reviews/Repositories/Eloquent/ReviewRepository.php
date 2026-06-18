<?php

declare(strict_types=1);

namespace App\Domains\Reviews\Repositories\Eloquent;

use App\Domains\Reviews\Entities\Review;
use App\Domains\Reviews\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Support\Collection;

final class ReviewRepository implements ReviewRepositoryInterface
{
    public function findById(string $id): ?Review
    {
        return Review::find($id);
    }

    public function findByBookingAndReviewer(string $bookingId, string $reviewerId): ?Review
    {
        return Review::where('booking_id', $bookingId)
            ->where('reviewer_id', $reviewerId)
            ->first();
    }

    public function getReviewsForUser(string $userId, int $limit = 10, int $offset = 0): Collection
    {
        return Review::where('reviewee_id', $userId)
            ->where('is_flagged', false)
            ->with('reviewer')
            ->orderByDesc('created_at')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    public function getAverageRatingForUser(string $userId): float
    {
        return (float) Review::where('reviewee_id', $userId)
            ->where('is_flagged', false)
            ->avg('rating') ?? 0.0;
    }

    public function save(Review $review): Review
    {
        $review->save();
        return $review;
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Reviews\Repositories\Contracts;

use App\Domains\Reviews\Entities\Review;
use Illuminate\Support\Collection;

interface ReviewRepositoryInterface
{
    public function findById(string $id): ?Review;
    public function findByBookingAndReviewer(string $bookingId, string $reviewerId): ?Review;
    public function getReviewsForUser(string $userId, int $limit = 10, int $offset = 0): Collection;
    public function getAverageRatingForUser(string $userId): float;
    public function save(Review $review): Review;
}

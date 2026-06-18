<?php

declare(strict_types=1);

namespace App\Domains\Reviews\Actions;

use App\Domains\Marketplace\Repositories\Contracts\BookingRepositoryInterface;
use App\Domains\Reviews\DTOs\SubmitReviewDTO;
use App\Domains\Reviews\Entities\Review;
use App\Domains\Reviews\Repositories\Contracts\ReviewRepositoryInterface;
use InvalidArgumentException;

final class SubmitReviewAction
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly ReviewRepositoryInterface $reviewRepository
    ) {}

    public function execute(SubmitReviewDTO $dto): Review
    {
        $booking = $this->bookingRepository->findById($dto->bookingId);

        if (!$booking) {
            throw new InvalidArgumentException('Booking not found');
        }

        if ($booking->status->value !== 'confirmed') { // In real app, might be 'completed'
            throw new InvalidArgumentException('Cannot review an incomplete booking');
        }

        // Determine who the reviewee is based on the reviewer
        $revieweeId = ($booking->sitter_id === $dto->reviewerId) 
            ? $booking->demand->owner_id 
            : $booking->sitter_id;

        // Check if review already exists
        if ($this->reviewRepository->findByBookingAndReviewer($dto->bookingId, $dto->reviewerId)) {
            throw new InvalidArgumentException('You have already reviewed this booking');
        }

        $review = new Review([
            'booking_id' => $dto->bookingId,
            'reviewer_id' => $dto->reviewerId,
            'reviewee_id' => $revieweeId,
            'rating' => $dto->rating,
            'content' => $dto->content,
            'is_flagged' => false,
        ]);

        return $this->reviewRepository->save($review);
    }
}

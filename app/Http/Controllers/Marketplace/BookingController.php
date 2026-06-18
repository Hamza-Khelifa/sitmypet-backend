<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketplace;

use App\Domains\Marketplace\Actions\CancelBookingAction;
use App\Domains\Marketplace\Repositories\Contracts\BookingRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly CancelBookingAction $cancelBookingAction
    ) {}

    public function show(string $id): JsonResponse
    {
        $booking = $this->bookingRepository->findById($id);
        
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json(['data' => $booking]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $success = $this->cancelBookingAction->execute($id);

        if (!$success) {
            return response()->json(['message' => 'Cannot cancel booking.'], 400);
        }

        return response()->json(['message' => 'Booking cancelled successfully.']);
    }
}

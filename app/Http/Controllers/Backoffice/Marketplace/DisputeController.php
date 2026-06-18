<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backoffice\Marketplace;

use App\Domains\Marketplace\Entities\Booking;
use App\Domains\Marketplace\Enums\BookingStatus;
use App\Domains\Payments\Entities\Wallet;
use App\Domains\Identity\Entities\AuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    /**
     * List all bookings for dispute resolution.
     */
    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with(['demand', 'sitter'])
            ->when($request->query('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($bookings);
    }

    /**
     * Force cancel a booking, overriding standard policies.
     */
    public function cancel(string $bookingId): JsonResponse
    {
        $booking = Booking::findOrFail($bookingId);
        
        $booking->status = BookingStatus::CANCELLED;
        $booking->save();

        event(new \App\Domains\Marketplace\Events\BookingForceCancelled($booking));

        return response()->json(['message' => 'Booking forcefully cancelled by Admin.']);
    }

    /**
     * Force refund an escrow wallet (requires super-admin).
     */
    public function refund(Request $request, string $bookingId): JsonResponse
    {
        if (!$request->user()->hasRole('super-admin')) {
            return response()->json(['message' => 'Only Super Admins can force refunds.'], 403);
        }

        $booking = Booking::findOrFail($bookingId);

        $transaction = \App\Domains\Payments\Entities\Transaction::where('reference_id', $bookingId)
            ->where('type', \App\Domains\Payments\Enums\TransactionType::PAY_IN->value)
            ->firstOrFail();

        $action = app(\App\Domains\Payments\Actions\RefundEscrowAction::class);
        $action->execute($transaction->id);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Forced refund for booking {$booking->id}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [],
        ]);

        return response()->json(['message' => 'Escrow refunded successfully.']);
    }
}

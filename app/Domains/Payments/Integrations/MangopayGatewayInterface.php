<?php

declare(strict_types=1);

namespace App\Domains\Payments\Integrations;

use App\Domains\Identity\Entities\SitterProfile;
use App\Domains\Marketplace\Entities\Booking;

interface MangopayGatewayInterface
{
    /**
     * Issues a direct refund for an escrow payload back to the owner's bank account.
     */
    public function refundEscrow(Booking $booking): bool;

    /**
     * Synchronizes a verified KYC document with the Mangopay ledger.
     */
    public function syncKycIdentity(SitterProfile $profile): bool;
}

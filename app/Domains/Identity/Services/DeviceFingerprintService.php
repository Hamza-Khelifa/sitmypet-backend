<?php

declare(strict_types=1);

namespace App\Domains\Identity\Services;

use App\Domains\Identity\Entities\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceFingerprintService
{
    /**
     * Generates a unique fingerprint based on request headers and IP.
     */
    public function generateFingerprint(Request $request): string
    {
        $components = [
            $request->ip(),
            $request->userAgent(),
            $request->header('Accept-Language', ''),
            // In a real app, you might use a frontend-generated UUID via Canvas Fingerprinting
        ];

        return hash('sha256', implode('|', $components));
    }

    /**
     * Identifies or creates a device record for tracking.
     */
    public function identifyDevice(string $userId, Request $request, string $fingerprint): UserDevice
    {
        /** @var UserDevice|null $device */
        $device = UserDevice::where('user_id', $userId)
            ->where('device_id', $fingerprint)
            ->first();

        if (!$device) {
            $device = UserDevice::create([
                'id' => (string) Str::uuid(),
                'user_id' => $userId,
                'device_id' => $fingerprint,
                'platform' => $this->parsePlatform($request->userAgent()),
                'last_active_at' => now(),
            ]);
        } else {
            $device->update([
                'last_active_at' => now(),
            ]);
        }

        return $device;
    }

    private function parseDeviceName(?string $userAgent): string
    {
        return 'Unknown Device'; // Simplified: Normally use jenssegers/agent
    }

    private function parsePlatform(?string $userAgent): string
    {
        return 'Unknown OS'; // Simplified
    }
}

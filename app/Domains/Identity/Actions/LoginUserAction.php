<?php

declare(strict_types=1);

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Entities\User;
use App\Domains\Identity\Repositories\Contracts\SessionRepositoryInterface;
use App\Domains\Identity\Repositories\Contracts\UserRepositoryInterface;
use App\Domains\Identity\Services\AuditLogService;
use App\Domains\Identity\Services\DeviceFingerprintService;
use App\Domains\Identity\Services\TokenFamilyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final readonly class LoginUserAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private SessionRepositoryInterface $sessionRepository,
        private TokenFamilyService $tokenFamilyService,
        private DeviceFingerprintService $deviceFingerprintService,
        private AuditLogService $auditLogService
    ) {
    }

    /**
     * Orchestrates the secure login flow.
     */
    public function execute(array $credentials, Request $request): array
    {
        // 1. Rate Limiting should be handled in HTTP Middleware, so we assume valid request volume here

        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            // Failed Login Attempt Logic (Normally dispatched as an event or logged)
            $this->auditLogService->log('LOGIN_FAILED', $user?->id, null, null, null, null, $request->ip(), $request->userAgent());
            throw new \Exception('Invalid credentials.');
        }

        if ($user->status->value === 'banned' || $user->status->value === 'deleted') {
            throw new \Exception('Account unavailable.');
        }

        return DB::transaction(function () use ($user, $request) {
            // 2. Device Fingerprinting
            $fingerprint = $this->deviceFingerprintService->generateFingerprint($request);
            $device = $this->deviceFingerprintService->identifyDevice($user->id, $request, $fingerprint);

            // 3. Sanctum Access Token (Short-lived)
            $tokenResult = $user->createToken(
                'Unknown Device',
                ['*'],
                now()->addMinutes(15) // Short-lived access
            );
            
            // Metadata extension (requires the migration we added)
            $tokenResult->accessToken->forceFill([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_name' => 'Unknown Device',
            ])->save();

            // 4. Token Family Service (Long-lived Refresh)
            $refreshToken = $this->tokenFamilyService->createInitialToken($user);

            // 5. User Session tracking (UI Active Sessions)
            $this->sessionRepository->create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'device_fingerprint' => $fingerprint,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_seen_at' => now(),
            ]);

            // 6. Audit Logging
            $this->auditLogService->log('LOGIN_SUCCESS', $user->id, 'User', $user->id);

            // 7. Update User Last Login
            $this->userRepository->update($user, ['last_login_at' => now()]);

            return [
                'user' => $user,
                'access_token' => $tokenResult->plainTextToken,
                'refresh_token' => $refreshToken->plainTextToken,
            ];
        });
    }
}

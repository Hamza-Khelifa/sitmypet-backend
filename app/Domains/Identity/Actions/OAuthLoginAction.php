<?php

declare(strict_types=1);

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Entities\User;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Repositories\Contracts\SocialAccountRepositoryInterface;
use App\Domains\Identity\Repositories\Contracts\UserRepositoryInterface;
use App\Domains\Identity\Services\OAuthProviderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class OAuthLoginAction
{
    public function __construct(
        private OAuthProviderService $oauthService,
        private SocialAccountRepositoryInterface $socialRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(string $provider): User
    {
        $providerUser = $this->oauthService->getProviderUser($provider);

        return DB::transaction(function () use ($provider, $providerUser) {
            $socialAccount = $this->socialRepository->findByProvider($provider, $providerUser->getId());

            if ($socialAccount) {
                return $socialAccount->user;
            }

            // Not found, check if email exists to link, or create new
            $user = $this->userRepository->findByEmail($providerUser->getEmail());

            if (!$user) {
                // Register new user via OAuth
                $user = $this->userRepository->create([
                    'id' => (string) Str::uuid(),
                    'email' => $providerUser->getEmail(),
                    'password' => Str::random(60), // Dummy password
                    'status' => UserStatus::PENDING_PROFILE_COMPLETION->value, // Skip OTP
                    'email_verified_at' => now(), // OAuth pre-verifies email
                ]);
                // Default role can be handled via frontend completing the profile
            }

            $this->socialRepository->create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $providerUser->getId(),
                'email' => $providerUser->getEmail(),
            ]);

            return $user;
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Identity\Services;

use Laravel\Socialite\Facades\Socialite;

class OAuthProviderService
{
    /**
     * Retrieves the social user from the provider via Laravel Socialite.
     */
    public function getProviderUser(string $provider): \Laravel\Socialite\Contracts\User
    {
        // Socialite handles the heavy lifting of OAuth2 callbacks
        return Socialite::driver($provider)->stateless()->user();
    }
}

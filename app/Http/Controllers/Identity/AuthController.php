<?php

declare(strict_types=1);

namespace App\Http\Controllers\Identity;

use App\Domains\Identity\Actions\LoginUserAction;
use App\Domains\Identity\Actions\RegisterUserAction;
use App\Domains\Identity\DTOs\UserRegistrationDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\LoginRequest;
use App\Http\Requests\Identity\RegisterRequest;
use App\Domains\Identity\Services\TokenFamilyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterUserAction $registerUserAction,
        private readonly LoginUserAction $loginUserAction,
        private readonly TokenFamilyService $tokenFamilyService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $dto = new UserRegistrationDTO(
            email: $validated['email'],
            password: $validated['password'],
            role: $validated['role'],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $user = $this->registerUserAction->execute($dto);

        return response()->json([
            'message' => 'User registered successfully. Please verify your email.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'status' => $user->status,
                ]
            ]
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->loginUserAction->execute(
                credentials: ['email' => $validated['email'], 'password' => $validated['password']],
                request: $request
            );

            return response()->json([
                'message' => 'Login successful.',
                'data' => [
                    'user' => [
                        'id' => $result['user']->id,
                        'email' => $result['user']->email,
                    ],
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.'
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'refresh_token' => ['required', 'string']
        ]);

        $refreshToken = $this->tokenFamilyService->rotateToken($validated['refresh_token'], $request->ip());

        if (!$refreshToken) {
            return response()->json([
                'message' => 'Invalid or expired refresh token. Please login again.'
            ], 401);
        }

        $user = $refreshToken->user;

        // Issue new short-lived access token
        $tokenResult = $user->createToken(
            'Unknown Device', // Can be enhanced by reading device id
            ['*'],
            now()->addMinutes(15)
        );

        $tokenResult->accessToken->forceFill([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_name' => 'Unknown Device',
        ])->save();

        return response()->json([
            'message' => 'Token refreshed successfully.',
            'data' => [
                'access_token' => $tokenResult->plainTextToken,
                'refresh_token' => $refreshToken->plainTextToken,
            ]
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Domains\Profile\Actions\CreateSitterProfileAction;
use App\Domains\Profile\DTOs\SitterProfileCreationDTO;
use App\Domains\Profile\Repositories\Contracts\SitterProfileRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\CreateSitterProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Profile\Entities\SitterProfile;

class SitterProfileController extends Controller
{
    public function __construct(
        private readonly SitterProfileRepositoryInterface $profileRepository,
        private readonly CreateSitterProfileAction $createAction
    ) {}

    /**
     * Create a new sitter profile for the authenticated user.
     */
    public function store(CreateSitterProfileRequest $request): JsonResponse
    {
        $dto = new SitterProfileCreationDTO(
            userId: $request->user()->id,
            bio: $request->validated('bio'),
            hourlyRate: (float) $request->validated('hourly_rate')
        );

        $profile = $this->createAction->execute($dto);

        return response()->json([
            'message' => 'Profile created successfully.',
            'data' => $profile
        ], 201);
    }

    /**
     * Display the specified sitter profile.
     */
    public function show(string $id): JsonResponse
    {
        $profile = $this->profileRepository->findById($id);

        if (!$profile) {
            return response()->json(['message' => 'Profile not found.'], 404);
        }

        return response()->json(['data' => $profile]);
    }

    /**
     * Upload and update the sitter profile avatar.
     */
    public function uploadAvatar(Request $request, SitterProfile $profile): JsonResponse
    {
        if ($profile->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'], // Max 5MB
        ]);

        $profile->addMediaFromRequest('avatar')
                ->toMediaCollection('avatars');

        return response()->json([
            'message' => 'Avatar uploaded successfully.',
            'avatar_url' => $profile->getFirstMediaUrl('avatars', 'avatar')
        ]);
    }
}

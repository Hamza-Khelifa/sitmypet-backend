<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Domains\Profile\Entities\Certification;
use App\Domains\Profile\Entities\SitterProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    /**
     * Store a new certification (e.g. KYC document) for the given profile.
     */
    public function store(Request $request, SitterProfile $profile): JsonResponse
    {
        // Ensure the user owns this profile
        if ($profile->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'type' => ['required', 'string'],
            'document_path' => ['required', 'string'],
        ]);

        $certification = $profile->certifications()->create([
            'type' => $validated['type'],
            'document_url' => $validated['document_path'],
            'status' => 'pending', // Assume 'pending' status maps to CertificationStatus::PENDING
        ]);

        return response()->json([
            'message' => 'Certification document submitted successfully.',
            'data' => $certification
        ], 201);
    }
}

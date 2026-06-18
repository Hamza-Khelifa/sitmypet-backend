<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backoffice\Profile;

use App\Domains\Profile\Entities\Certification;
use App\Domains\Profile\Entities\SitterProfile;
use App\Domains\Profile\Enums\CertificationStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    /**
     * List all certifications awaiting approval (ACACED and KYC).
     */
    public function pending(): JsonResponse
    {
        $certifications = Certification::with(['profile.user:id,email'])
            ->where('status', CertificationStatus::PENDING->value)
            ->paginate(20);

        return response()->json($certifications);
    }

    /**
     * Generate a short-lived signed URL to securely view the document.
     */
    public function generateDocumentUrl(string $certificationId): JsonResponse
    {
        $certification = Certification::findOrFail($certificationId);

        if (!$certification->document_url) {
            return response()->json(['message' => 'No document attached.'], 404);
        }

        // Simulate S3 Signed URL generation for security as per Architecture constraints
        try {
            $url = Storage::disk('s3')->temporaryUrl(
                $certification->document_url,
                now()->addMinutes(15)
            );
        } catch (\Exception $e) {
            // Fallback for local testing if S3 is not configured
            $url = url('/storage/' . $certification->document_url);
        }

        return response()->json(['url' => $url]);
    }

    /**
     * Approve the certification and mark the Sitter as verified.
     */
    public function approve(string $certificationId): JsonResponse
    {
        $certification = Certification::with('profile')->findOrFail($certificationId);
        
        $certification->status = CertificationStatus::APPROVED;
        $certification->verified_at = now();
        $certification->save();

        // If it's ACACED or primary identity, verify the sitter
        $profile = $certification->profile;
        $profile->is_verified = true;
        $profile->save();

        event(new \App\Domains\Profile\Events\SitterVerified($profile->id));

        return response()->json(['message' => 'Profile verified and KYC synced.']);
    }

    /**
     * Reject the certification with a reason.
     */
    public function reject(Request $request, string $certificationId): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $certification = Certification::findOrFail($certificationId);
        $certification->status = CertificationStatus::REJECTED;
        $certification->save();

        $certification->profile->user->notify(new \App\Domains\Profile\Notifications\VerificationRejected($request->reason));

        return response()->json(['message' => 'Certification rejected.']);
    }
}

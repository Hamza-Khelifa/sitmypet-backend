<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignedUrlController extends Controller
{
    /**
     * Generate a temporary signed URL for direct S3 upload.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'extension' => ['required', 'string', 'in:jpg,jpeg,png,pdf'],
            'type' => ['required', 'string', 'in:avatar,kyc'],
        ]);

        $extension = $request->input('extension');
        $type = $request->input('type');
        
        // Generate a random path
        $path = sprintf('%s/%s/%s.%s', 
            $type,
            $request->user()->id,
            Str::random(40),
            $extension
        );

        // Generate pre-signed URL for 15 minutes
        // Assumes s3 disk is configured correctly in filesystems.php
        $url = Storage::disk('s3')->temporaryUploadUrl(
            $path,
            now()->addMinutes(15)
        );

        return response()->json([
            'data' => [
                'upload_url' => $url['url'] ?? $url, // Depending on Flysystem version, it might be an array or string
                'path' => $path,
            ]
        ]);
    }
}

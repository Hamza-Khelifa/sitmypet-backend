<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Identity;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceSessionController extends Controller
{
    /**
     * List all active sessions (tokens) for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->get(['id', 'name', 'last_used_at', 'created_at']);
        
        return response()->json([
            'sessions' => $tokens
        ]);
    }

    /**
     * Revoke a specific session (token).
     */
    public function destroy(Request $request, string $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->where('id', $tokenId)->firstOrFail();
        
        // Find associated session or refresh token based on token ID if possible
        // Or simpler: We can revoke the current session
        $token->delete();

        return response()->json([
            'message' => 'Session revoked successfully.'
        ]);
    }
}

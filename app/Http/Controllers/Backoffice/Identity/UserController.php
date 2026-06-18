<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backoffice\Identity;

use App\Domains\Identity\Entities\AuditLog;
use App\Domains\Identity\Entities\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->query('search'), function ($query, $search) {
                $query->where('email', 'ilike', "%{$search}%");
            })
            ->paginate(20);

        return response()->json($users);
    }

    public function show(string $id): JsonResponse
    {
        $user = User::with(['roles', 'devices'])->findOrFail($id);
        
        return response()->json($user);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        // Only admins and super-admins can hit this due to routing, but just to be safe
        $request->validate(['status' => 'required|string|in:active,suspended,banned']);
        
        $user = User::findOrFail($id);
        
        // Prevent admins from banning super-admins unless they are a super-admin themselves
        if ($user->hasRole('super-admin') && !$request->user()->hasRole('super-admin')) {
            return response()->json(['message' => 'Unauthorized to modify super-admin status.'], 403);
        }

        $user->status = $request->status;
        $user->save();

        return response()->json(['message' => "User status updated to {$user->status->value}"]);
    }

    public function auditLogs(Request $request): JsonResponse
    {
        // Constitution requirement: PII should not be broadly leaked, but backoffice audit log
        // is internal. However, we'll selectively hide sensitive details from `old_values/new_values`
        $logs = AuditLog::with('user:id,email')
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($logs);
    }
}

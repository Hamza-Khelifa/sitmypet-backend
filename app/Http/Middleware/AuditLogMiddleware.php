<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domains\Identity\Entities\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log mutating requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $request->user()) {
            
            // Mask sensitive data
            $payload = $request->except(['password', 'password_confirmation', 'token']);
            
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => $request->method() . ' ' . $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => $payload,
            ]);
        }

        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. API token required.',
            ], 401);
        }

        $apiToken = ApiToken::where('token', hash('sha256', $token))
            ->with('user')
            ->first();

        if (!$apiToken || !$apiToken->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API token.',
            ], 401);
        }

        // Record token usage
        $apiToken->recordUsage($request->ip());

        // Set authenticated user
        auth()->setUser($apiToken->user);
        $request->merge(['api_token' => $apiToken]);

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Generate API token
     */
    public function generateToken(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'token_name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        $user = \App\Models\User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $plainTextToken = ApiToken::generateToken();
        $hashedToken = hash('sha256', $plainTextToken);

        $expiresAt = isset($validated['expires_in_days'])
            ? now()->addDays($validated['expires_in_days'])
            : null;

        $apiToken = ApiToken::create([
            'user_id' => $user->id,
            'name' => $validated['token_name'],
            'token' => $hashedToken,
            'abilities' => $validated['abilities'] ?? ['*'],
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'API token generated successfully.',
            'data' => [
                'token' => $plainTextToken,
                'token_id' => $apiToken->id,
                'name' => $apiToken->name,
                'expires_at' => $apiToken->expires_at?->toISOString(),
            ],
        ], 201);
    }

    /**
     * Revoke API token
     */
    public function revokeToken(Request $request)
    {
        $validated = $request->validate([
            'token_id' => 'required|exists:api_tokens,id',
        ]);

        $token = ApiToken::where('id', $validated['token_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $token->revoke();

        return response()->json([
            'success' => true,
            'message' => 'API token revoked successfully.',
        ]);
    }

    /**
     * List user's API tokens
     */
    public function listTokens(Request $request)
    {
        $tokens = ApiToken::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tokens->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->toISOString(),
                    'usage_count' => $token->usage_count,
                    'is_active' => $token->is_active,
                    'expires_at' => $token->expires_at?->toISOString(),
                    'created_at' => $token->created_at->toISOString(),
                ];
            }),
        ]);
    }

    /**
     * Verify token
     */
    public function verifyToken(Request $request)
    {
        $apiToken = $request->api_token;

        return response()->json([
            'success' => true,
            'message' => 'Token is valid.',
            'data' => [
                'user' => [
                    'id' => auth()->user()->id,
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'role' => auth()->user()->role->name,
                ],
                'token_name' => $apiToken->name,
                'abilities' => $apiToken->abilities,
            ],
        ]);
    }
}
